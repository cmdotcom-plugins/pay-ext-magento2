<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Model\Data\PaymentInterface as CMPaymentDataInterface;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Service\OrderTransactionServiceInterface;
use CM\Payments\Client\Api\OrderInterface as CMOrderClientInterface;
use CM\Payments\Client\Model\Response\OrderDetail;
use CM\Payments\Client\Model\Response\Payment\Authorization;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Logger\CMPaymentsLogger;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use CM\Payments\Api\Model\Data\OrderInterface as CMDataOrderInterface;

class OrderTransactionService implements OrderTransactionServiceInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CMOrderRepositoryInterface
     */
    private $cmOrderRepository;

    /**
     * @var CMPaymentRepositoryInterface
     */
    private $cmPaymentRepository;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * @var CMOrderClientInterface
     */
    private $orderClient;

    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var CMPaymentDataFactory
     */
    private $cmPaymentDataFactory;

    /**
     * OrderTransactionService constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param CMPaymentRepositoryInterface $cmPaymentRepository
     * @param CMPaymentsLogger $cmPaymentsLogger
     * @param CMOrderClientInterface $orderClient
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CMOrderRepositoryInterface $cmOrderRepository,
        CMPaymentRepositoryInterface $cmPaymentRepository,
        CMPaymentDataFactory $cmPaymentDataFactory,
        CMPaymentsLogger $cmPaymentsLogger,
        CMOrderClientInterface $orderClient,
        ManagerInterface $eventManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->cmPaymentRepository = $cmPaymentRepository;
        $this->logger = $cmPaymentsLogger;
        $this->eventManager = $eventManager;
        $this->orderClient = $orderClient;
        $this->cmPaymentDataFactory = $cmPaymentDataFactory;
    }

    /**
     * @inheritDoc
     */
    public function process(string $orderReference): void
    {
        $cmOrder = $this->cmOrderRepository->getByIncrementId($orderReference);

        /** @var OrderInterface $order */
        $order = $this->orderRepository->get($cmOrder->getOrderId());

        if ($order->getState() === Order::STATE_CLOSED) {
            $this->logger->info('Don\'t need to update order because state is already closed '. $orderReference);
            return;
        }

        /** @var Payment $payment */
        $payment = $order->getPayment();

        if ($payment->getTransactionId() && $order->getState() === Order::STATE_PROCESSING) {
            $this->logger->info('Don\'t need to update order because state is already in progress '. $orderReference);
            return;
        }

        try {
            $cmOrderDetails = $this->orderClient->getDetail($cmOrder->getOrderKey());
            $this->logger->info('CM Order is safe '. $cmOrderDetails->isSafe());
        } catch (RequestException $exception) {
            $this->logger->error($exception->getMessage());
            throw new NoSuchEntityException(
                __('CM Order with ID "%1" does not exist.', $orderReference)
            );
        }

        if (empty($cmOrderDetails->getConsideredSafe()) || ! $cmOrderDetails->isSafe()) {
            $this->cancelOrderByPaymentStatus($cmOrder, $order, $cmOrderDetails);

            // If order is not considered 'Safe' we don't have to process.
            return;
        }

        $this->eventManager->dispatch('cmpayments_before_process_transaction', [
            'order' => $order,
            'cmOrderDetails' => $cmOrderDetails
        ]);

        // Todo: move to separate method or class
        $this->createCMPaymentIfNotExists($cmOrder, $order, $cmOrderDetails);

        $this->logger->info('Create invoice and transaction for order '. $orderReference);
        $this->logger->info('CM payment id'. $cmOrderDetails->getAuthorizedPayment()->getId());

        $this->capture($payment, $cmOrderDetails, $order);

        $this->eventManager->dispatch('cmpayments_after_process_transaction', [
            'order' => $order,
            'cmOrderDetails' => $cmOrderDetails
        ]);
    }

    /**
     * Capture payment and change order state to processing
     * @param Payment $payment
     * @param OrderDetail $cmOrderDetails
     * @param OrderInterface $order
     */
    private function capture(Payment $payment, OrderDetail $cmOrderDetails, OrderInterface $order): void
    {
        $payment->setTransactionId($cmOrderDetails->getAuthorizedPayment()->getId());
        $payment->setCurrencyCode($order->getBaseCurrencyCode());
        $payment->setNotificationResult(true);
        $payment->registerCaptureNotification($order->getBaseGrandTotal(), true);
        $payment->setIsTransactionClosed(true);
        $order->setState(Order::STATE_PROCESSING);

        if (Order::STATE_PROCESSING === $order->getState()) {
            $order->addCommentToStatusHistory(__('Order processed by CM.'), Order::STATE_PROCESSING);
        }

        $this->orderRepository->save($order);
    }

    /**
     * Save CM Payment model in database if not exists
     * @param CMDataOrderInterface $cmOrder
     * @param OrderInterface $order
     * @param OrderDetail $cmOrderDetails
     */
    private function createCMPaymentIfNotExists(
        CMDataOrderInterface $cmOrder,
        OrderInterface $order,
        OrderDetail $cmOrderDetails
    ): void {
        try {
            $this->cmPaymentRepository->getByOrderKey($cmOrder->getOrderKey());
        } catch (NoSuchEntityException $exception) {
            /** @var CMPaymentDataInterface $cmPayment */
            $cmPayment = $this->cmPaymentDataFactory->create();
            $cmPayment->setOrderId((int)$order->getEntityId());
            $cmPayment->setOrderKey($cmOrder->getOrderKey());
            $cmPayment->setIncrementId($order->getIncrementId());
            $cmPayment->setPaymentId($cmOrderDetails->getAuthorizedPayment()->getId());

            $this->cmPaymentRepository->save($cmPayment);
        }
    }

    /**
     * Cancel order by payment status for specific payment methods
     * If payment method is credit card and cm payment model exists we need to cancel the order when payment failed
     * the order status will be checked on the client side, if cancelled we need to redirect the user.
     * @param CMDataOrderInterface $cmOrder
     * @param OrderInterface $order
     * @param OrderDetail $cmOrderDetails
     */
    private function cancelOrderByPaymentStatus(
        CMDataOrderInterface $cmOrder,
        OrderInterface $order,
        OrderDetail $cmOrderDetails
    ): void {
        try {
            $cmPayment = $this->cmPaymentRepository->getByOrderKey($cmOrder->getOrderKey());
            if ($order->getPayment()->getMethod() === 'cm_payments_creditcard' && $cmPayment) {
                foreach ($cmOrderDetails->getPayments() as $payment) {
                    if ($payment->getAuthorization()->getState() !== Authorization::STATE_AUTHORIZED) {
                        $order->setState(Order::STATE_CANCELED);
                        $order->addCommentToStatusHistory(
                            __('Order cancelled by CM, payment id %1', $payment->getId()),
                            Order::STATE_CANCELED
                        );
                        $this->orderRepository->save($order);
                    }
                }
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
        }
    }
}

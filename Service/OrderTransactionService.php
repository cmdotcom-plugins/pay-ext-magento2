<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderTransactionServiceInterface;
use CM\Payments\Client\Api\OrderInterface as CMOrderClientInterface;
use CM\Payments\Logger\CMPaymentsLogger;
use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

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
     * OrderTransactionService constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param CMPaymentsLogger $cmPaymentsLogger
     * @param CMOrderClientInterface $orderClient
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CMOrderRepositoryInterface $cmOrderRepository,
        CMPaymentsLogger $cmPaymentsLogger,
        CMOrderClientInterface $orderClient,
        ManagerInterface $eventManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->logger = $cmPaymentsLogger;
        $this->eventManager = $eventManager;
        $this->orderClient = $orderClient;
    }

    /**
     * @inheritDoc
     */
    public function process(string $orderReference): void
    {
        $cmOrder = $this->cmOrderRepository->getByIncrementId($orderReference);

        /** @var OrderInterface $order */
        $order = $this->orderRepository->get($cmOrder->getOrderId());

        /** @var Payment $payment */
        $payment = $order->getPayment();

        try {
            $cmOrderDetails = $this->orderClient->getDetail($cmOrder->getOrderKey());
        } catch (RequestException $exception) {
            $this->logger->error($exception->getMessage());
            throw new NoSuchEntityException(
                __('CM Order with ID "%1" does not exist.', $orderReference)
            );
        }

        if (empty($cmOrderDetails->getConsideredSafe()) || ! $cmOrderDetails->isSafe()) {
            // If order is not considered 'Safe' we don't have to process.
            return;
        }

        $this->eventManager->dispatch('cmpayments_before_process_transaction', [
            'order' => $order,
            'cmOrderDetails' => $cmOrderDetails
        ]);

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

        $this->eventManager->dispatch('cmpayments_after_process_transaction', [
            'order' => $order,
            'cmOrderDetails' => $cmOrderDetails
        ]);
    }
}

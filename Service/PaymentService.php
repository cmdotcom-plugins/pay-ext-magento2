<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use CM\Payments\Api\Model\Data\PaymentInterface as CMPaymentDataInterface;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Model\Domain\PaymentOrderStatusInterfaceFactory;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Api\Service\PaymentRequestBuilderInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Api\Model\Domain\PaymentOrderStatusInterface;
use CM\Payments\Client\Api\CMPaymentInterface;
use CM\Payments\Client\Api\PaymentInterface as CMPaymentClientInterface;
use CM\Payments\Client\Model\CMPaymentFactory;
use CM\Payments\Exception\EmptyPaymentIdException;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\ConfigProvider;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;

class PaymentService implements PaymentServiceInterface
{
    /**
     * @var CMPaymentClientInterface
     */
    private $paymentClient;

    /**
     * @var CMPaymentFactory
     */
    private $cmPaymentFactory;

    /**
     * @var PaymentRequestBuilderInterface
     */
    private $paymentRequestBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CMPaymentRepositoryInterface
     */
    private $cmPaymentRepository;

    /**
     * @var CMPaymentDataFactory
     */
    private $cmPaymentDataFactory;

    /**
     * @var CMOrderRepositoryInterface
     */
    private $cmOrderRepository;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var PaymentOrderStatusInterfaceFactory
     */
    private $paymentOrderStatusFactory;

    /**
     * PaymentService constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param CMPaymentClientInterface $paymentClient
     * @param PaymentRequestBuilderInterface $paymentRequestBuilder
     * @param CMPaymentDataFactory $cmPaymentDataFactory
     * @param OrderServiceInterface $orderService
     * @param CMPaymentFactory $cmPaymentFactory
     * @param CMPaymentRepositoryInterface $cmPaymentRepository
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param ManagerInterface $eventManager
     * @param CMPaymentsLogger $logger
     * @param CustomerFactory $customerFactory
     * @param PaymentOrderStatusInterfaceFactory $paymentOrderStatusFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CMPaymentClientInterface $paymentClient,
        PaymentRequestBuilderInterface $paymentRequestBuilder,
        CMPaymentDataFactory $cmPaymentDataFactory,
        OrderServiceInterface $orderService,
        CMPaymentFactory $cmPaymentFactory,
        CMPaymentRepositoryInterface $cmPaymentRepository,
        CMOrderRepositoryInterface $cmOrderRepository,
        ManagerInterface $eventManager,
        CMPaymentsLogger $logger,
        CustomerFactory $customerFactory,
        PaymentOrderStatusInterfaceFactory $paymentOrderStatusFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentClient = $paymentClient;
        $this->paymentRequestBuilder = $paymentRequestBuilder;
        $this->cmPaymentDataFactory = $cmPaymentDataFactory;
        $this->cmPaymentFactory = $cmPaymentFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->cmPaymentRepository = $cmPaymentRepository;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->orderService = $orderService;
        $this->customerFactory = $customerFactory;
        $this->paymentOrderStatusFactory = $paymentOrderStatusFactory;
    }

    /**
     * @inheritDoc
     */
    public function create(
        int $orderId,
        CardDetailsInterface $cardDetails,
        BrowserDetailsInterface $browserDetails
    ): CMPaymentInterface {
        $order = $this->orderRepository->get($orderId);

        // if cmOrder not yet exists we need to create one.
        try {
            $cmOrder = $this->cmOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $exception) {
            $cmOrder = $this->orderService->create($orderId);
        }
        $paymentCreateRequest = $this->paymentRequestBuilder->create(
            $order->getIncrementId(),
            $cmOrder->getOrderKey(),
            $order,
            $cardDetails,
            $browserDetails
        );

        $this->logger->info(
            'CM Create payment request',
            [
                'orderId' => $orderId,
                'requestPayload' => $paymentCreateRequest->getPayload()
            ]
        );

        $this->eventManager->dispatch('cmpayments_before_payment_create', [
            'order' => $order,
            'paymentCreateRequest' => $paymentCreateRequest,
        ]);

        $paymentCreateResponse = null;
        try {
            $paymentCreateResponse = $this->paymentClient->create(
                $paymentCreateRequest
            );
        } catch (GuzzleException $e) {
            $this->logger->info(
                'CM Create payment request error',
                [
                    'orderId' => $orderId,
                    'exceptionMessage' => $e->getMessage()
                ]
            );
        }

        // Cleaning of ELV iban from payment information
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        if ($order->getPayment()->getMethod() == ConfigProvider::CODE_ELV) {
            unset($additionalInformation['iban']);
            $order->getPayment()->setAdditionalInformation($additionalInformation);
            $this->orderRepository->save($order);
        }

        // Todo: validate and handle response status
        if (!$paymentCreateResponse || !$paymentCreateResponse->getId()) {
            throw new EmptyPaymentIdException(__('Empty payment id'));
        }

        /** @var CMPaymentDataInterface $cmPayment */
        $cmPayment = $this->cmPaymentDataFactory->create();
        $cmPayment->setOrderId((int)$order->getEntityId());
        $cmPayment->setOrderKey($cmOrder->getOrderKey());
        $cmPayment->setIncrementId($order->getIncrementId());
        $cmPayment->setPaymentId($paymentCreateResponse->getId());

        $this->cmPaymentRepository->save($cmPayment);

        $additionalInformation['cm_payment_id'] = $paymentCreateResponse->getId();
        $order->getPayment()->setAdditionalInformation($additionalInformation);
        $this->orderRepository->save($order);

        $cmPayment = $this->cmPaymentFactory->create(
            [
                'id' => $paymentCreateResponse->getId(),
                'status' => $paymentCreateResponse->getStatus(),
                'redirectUrl' => $paymentCreateResponse->getRedirectUrl(),
                'urls' => $paymentCreateResponse->getUrls()
            ]
        );

        $this->eventManager->dispatch('cmpayments_after_payment_create', [
            'order' => $order,
            'cmPayment' => $cmPayment,
        ]);

        return $cmPayment;
    }

    /**
     * @param string $paymentId
     * @return PaymentOrderStatusInterface
     */
    public function getPaymentStatus(string $paymentId): PaymentOrderStatusInterface
    {
        $cmPayment = $this->cmPaymentRepository->getByPaymentId($paymentId);
        $order = $this->orderRepository->get($cmPayment->getOrderId());

        return $this->paymentOrderStatusFactory->create([
            'orderId' => $order->getIncrementId(),
            'status' => $order->getStatus()
        ]);
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Model\Data\PaymentInterface as CMPaymentDataInterface;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Service\PaymentRequestBuilderInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Api\CMPaymentInterface;
use CM\Payments\Client\Api\PaymentInterface as CMPaymentClientInterface;
use CM\Payments\Client\Model\CMPaymentFactory;
use CM\Payments\Exception\EmptyPaymentIdException;
use CM\Payments\Logger\CMPaymentsLogger;
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
     * PaymentService constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param CMPaymentClientInterface $paymentClient
     * @param PaymentRequestBuilderInterface $paymentRequestBuilder
     * @param CMPaymentDataFactory $cmPaymentDataFactory
     * @param CMPaymentFactory $cmPaymentFactory
     * @param CMPaymentRepositoryInterface $cmPaymentRepository
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param CMPaymentsLogger $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CMPaymentClientInterface $paymentClient,
        PaymentRequestBuilderInterface $paymentRequestBuilder,
        CMPaymentDataFactory $cmPaymentDataFactory,
        CMPaymentFactory $cmPaymentFactory,
        CMPaymentRepositoryInterface $cmPaymentRepository,
        CMOrderRepositoryInterface $cmOrderRepository,
        CMPaymentsLogger $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentClient = $paymentClient;
        $this->paymentRequestBuilder = $paymentRequestBuilder;
        $this->cmPaymentDataFactory = $cmPaymentDataFactory;
        $this->cmPaymentFactory = $cmPaymentFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->cmPaymentRepository = $cmPaymentRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): CMPaymentInterface
    {
        $order = $this->orderRepository->get($orderId);
        $cmOrder = $this->cmOrderRepository->getByOrderId((int)$order->getEntityId());
        $paymentCreateRequest = $this->paymentRequestBuilder->create($order, $cmOrder->getOrderKey());

        $this->logger->info(
            'CM Create payment request',
            [
                'orderId' => $orderId,
                'requestPayload' => $paymentCreateRequest->getPayload()
            ]
        );

        $paymentCreateResponse = $this->paymentClient->create(
            $paymentCreateRequest
        );

        // Todo: validate and handle response status
        if (!$paymentCreateResponse->getId()) {
            throw new EmptyPaymentIdException(__('Empty payment id'));
        }

        /** @var CMPaymentDataInterface $cmPayment */
        $cmPayment = $this->cmPaymentDataFactory->create();
        $cmPayment->setOrderId((int)$order->getEntityId());
        $cmPayment->setOrderKey($cmOrder->getOrderKey());
        $cmPayment->setIncrementId($order->getIncrementId());
        $cmPayment->setPaymentId($paymentCreateResponse->getId());

        $this->cmPaymentRepository->save($cmPayment);

        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        $additionalInformation['cm_payment_id'] = $paymentCreateResponse->getId();

        $order->getPayment()->setAdditionalInformation($additionalInformation);
        $this->orderRepository->save($order);

        return $this->cmPaymentFactory->create(
            [
                'id' => $paymentCreateResponse->getId(),
                'status' => $paymentCreateResponse->getStatus(),
                'redirectUrl' => $paymentCreateResponse->getRedirectUrl(),
                'urls' => $paymentCreateResponse->getUrls()
            ]
        );
    }
}

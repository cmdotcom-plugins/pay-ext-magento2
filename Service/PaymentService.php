<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Model\Data\OrderInterfaceFactory;
use CM\Payments\Api\Model\Data\PaymentInterface as CMPaymentDataInterface;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Service\PaymentRequestBuilderInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Api\CMPaymentInterface;
use CM\Payments\Client\Model\CMPaymentFactory;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Client\Model\CMPaymentUrlFactory;
use CM\Payments\Exception\EmptyPaymentIdException;
use Magento\Sales\Api\OrderRepositoryInterface;

class PaymentService implements PaymentServiceInterface
{
    /**
     * @var ApiClientInterface
     */
    private $apiClient;
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
     * @var CMPaymentUrlFactory
     */
    private $cmPaymentUrlFactory;
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
     * OrderService constructor
     *
     * @param ApiClientInterface $apiClient
     * @param OrderInterfaceFactory $cmOrderFactory
     * @param CMOrderRepositoryInterface $CMOrderRepository
     * @param CMOrderFactory $cmOrderFactory
     * @param CMOrderRepositoryInterface $cmOrderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ApiClientInterface $apiClient,
        PaymentRequestBuilderInterface $paymentRequestBuilder,
        CMPaymentDataFactory $cmPaymentDataFactory,
        CMPaymentFactory $cmPaymentFactory,
        CMPaymentUrlFactory $cmPaymentUrlFactory,
        CMPaymentRepositoryInterface $cmPaymentRepository,
        CMOrderRepositoryInterface $cmOrderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->apiClient = $apiClient;
        $this->paymentRequestBuilder = $paymentRequestBuilder;
        $this->cmPaymentFactory = $cmPaymentFactory;
        $this->cmPaymentUrlFactory = $cmPaymentUrlFactory;
        $this->cmPaymentRepository = $cmPaymentRepository;
        $this->cmPaymentDataFactory = $cmPaymentDataFactory;
        $this->cmOrderRepository = $cmOrderRepository;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): CMPaymentInterface
    {
        $order = $this->orderRepository->get($orderId);
        $cmOrder = $this->cmOrderRepository->getByOrderId((int) $order->getEntityId());
        $paymentCreateRequest = $this->paymentRequestBuilder->create($order, $cmOrder->getOrderKey());

        $response = $this->apiClient->execute(
            $paymentCreateRequest
        );

        // Todo: validate and handle response status
        if (empty($response['id'])) {
            throw new EmptyPaymentIdException(__('Empty payment id'));
        }

        /** @var CMPaymentDataInterface $cmPayment */
        $cmPayment = $this->cmPaymentDataFactory->create();
        $cmPayment->setOrderId((int)$order->getEntityId());
        $cmPayment->setOrderKey($cmOrder->getOrderKey());
        $cmPayment->setIncrementId($order->getIncrementId());
        $cmPayment->setPaymentId($response['id']);

        $this->cmPaymentRepository->save($cmPayment);

        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        $additionalInformation['cm_payment_id'] = $response['id'];

        $order->getPayment()->setAdditionalInformation($additionalInformation);
        $this->orderRepository->save($order);

        $urls = [];
        if (!empty($response['urls'])) {
            foreach ($response['urls'] as $url) {
                $urls[] = $this->cmPaymentUrlFactory->create([
                    'purpose' => $url['purpose'],
                    'method' => $url['method'],
                    'url' => $url['url'],
                    'order' => $url['order'],
                ]);
            }
        }

        return $this->cmPaymentFactory->create([
            'id' => $response['id'],
            'status' => $response['status'],
            'urls' => $urls
        ]);
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Model\Data\OrderInterface as CMOrder;
use CM\Payments\Api\Model\Data\OrderInterfaceFactory as CMOrderFactory;
use CM\Payments\Api\Model\Domain\CMOrderInterface;
use CM\Payments\Api\Model\Domain\CMOrderInterfaceFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Api\OrderInterface as CMOrderClientInterface;
use CM\Payments\Exception\EmptyOrderKeyException;
use CM\Payments\Logger\CMPaymentsLogger;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderService implements OrderServiceInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CMOrderClientInterface
     */
    private $orderClient;

    /**
     * @var CMOrderFactory
     */
    private $cmOrderFactory;

    /**
     * @var CMOrderRepositoryInterface
     */
    private $cmOrderRepository;

    /**
     * @var OrderRequestBuilderInterface
     */
    private $orderRequestBuilder;

    /**
     * @var CMOrderInterfaceFactory
     */
    private $cmOrderInterfaceFactory;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * OrderService constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param CMOrderClientInterface $orderClient
     * @param CMOrderFactory $cmOrderFactory
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param OrderRequestBuilderInterface $orderRequestBuilder
     * @param CMOrderInterfaceFactory $cmOrderInterfaceFactory
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CMOrderClientInterface $orderClient,
        CMOrderFactory $cmOrderFactory,
        CMOrderRepositoryInterface $cmOrderRepository,
        OrderRequestBuilderInterface $orderRequestBuilder,
        CMOrderInterfaceFactory $cmOrderInterfaceFactory,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderClient = $orderClient;
        $this->cmOrderFactory = $cmOrderFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->orderRequestBuilder = $orderRequestBuilder;
        $this->cmOrderInterfaceFactory = $cmOrderInterfaceFactory;
        $this->logger = $cmPaymentsLogger;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): CMOrderInterface
    {
        $order = $this->orderRepository->get($orderId);
        $orderCreateRequest = $this->orderRequestBuilder->create($order);

        $this->logger->info(
            'CM Create order request',
            [
                'orderId' => $orderId,
                'requestPayload' => $orderCreateRequest->getPayload()
            ]
        );

        $orderCreateResponse = $this->orderClient->create(
            $orderCreateRequest
        );

        if (empty($orderCreateResponse->getOrderKey())) {
            throw new EmptyOrderKeyException(__('Empty order key'));
        }

        /** @var CMOrder $cmOrder */
        $model = $this->cmOrderFactory->create();
        $model->setOrderId((int)$order->getEntityId());
        $model->setOrderKey($orderCreateResponse->getOrderKey());
        $model->setIncrementId($order->getIncrementId());

        $this->cmOrderRepository->save($model);

        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        if ($orderCreateResponse->getExpiresOn()) {
            $additionalInformation['expires_at'] = $orderCreateResponse->getExpiresOn();
        }

        if ($orderCreateResponse->getUrl()) {
            $additionalInformation['checkout_url'] = $orderCreateResponse->getUrl();
        }

        $order->getPayment()->setAdditionalInformation($additionalInformation);
        $this->orderRepository->save($order);

        return $this->cmOrderInterfaceFactory->create(
            [
                'url' => $orderCreateResponse->getUrl(),
                'orderReference' => $orderCreateRequest->getPayload()['order_reference'],
                'orderKey' => $orderCreateResponse->getOrderKey(),
                'expiresOn' => $orderCreateResponse->getExpiresOn()
            ]
        );
    }
}

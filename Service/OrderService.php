<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Model\Data\OrderInterfaceFactory;
use CM\Payments\Api\Model\Domain\CMOrderInterface;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Exception\EmptyOrderKeyException;
use Magento\Sales\Api\OrderRepositoryInterface;
use CM\Payments\Api\Model\Domain\CMOrderInterfaceFactory;

class OrderService implements OrderServiceInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var ApiClientInterface
     */
    private $apiClient;
    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;
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
     * OrderService constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param ApiClientInterface $apiClient
     * @param OrderInterfaceFactory $orderFactory
     * @param CMOrderRepositoryInterface $CMOrderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ApiClientInterface $apiClient,
        OrderInterfaceFactory $orderFactory,
        CMOrderRepositoryInterface $cmOrderRepository,
        OrderRequestBuilderInterface $orderRequestBuilder,
        CMOrderInterfaceFactory $cmOrderInterfaceFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->apiClient = $apiClient;
        $this->orderFactory = $orderFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->orderRequestBuilder = $orderRequestBuilder;
        $this->cmOrderInterfaceFactory = $cmOrderInterfaceFactory;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): CMOrderInterface
    {
        $order = $this->orderRepository->get($orderId);
        $orderRequest = $this->orderRequestBuilder->create($order);
        $response = $this->apiClient->execute(
            $orderRequest
        );

        if (empty($response['order_key'])) {
            throw new EmptyOrderKeyException('Empty order key');
        }

        $model = $this->orderFactory->create();
        $model->setOrderId((int)$order->getEntityId());
        $model->setOrderKey($response['order_key']);
        $model->setIncrementId($order->getIncrementId());

        $this->cmOrderRepository->save($model);

        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        if ($response['expires_on']) {
            $additionalInformation['expires_at'] = $response['expires_on'];
        }

        if ($response['url']) {
            $additionalInformation['checkout_url'] = $response['url'];
        }

        $order->getPayment()->setAdditionalInformation($additionalInformation);
        $this->orderRepository->save($order);

        return $this->cmOrderInterfaceFactory->create([
            'url' => $response['url'],
            'orderReference' => $orderRequest->getPayload()['order_reference'],
            'orderKey' => $response['order_key'],
            'expiresOn' => $response['expires_on'],
        ]);
    }
}

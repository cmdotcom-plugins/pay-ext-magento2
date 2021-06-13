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
use CM\Payments\Api\Model\Data\OrderInterface as CMOrder;
use CM\Payments\Api\Model\Data\OrderInterfaceFactory as CMOrderFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Exception\EmptyOrderKeyException;
use Magento\Sales\Api\OrderRepositoryInterface;
use CM\Payments\Api\Model\Domain\CMOrderInterfaceFactory;
use CM\Payments\Client\Request\OrderGetRequest;
use CM\Payments\Client\Request\OrderGetRequestFactory;
use Magento\Sales\Model\Order;

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
     * @var OrderGetRequestFactory
     */
    private $orderGetRequestFactory;

    /**
     * OrderService constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param ApiClientInterface $apiClient
     * @param OrderInterfaceFactory $cmOrderFactory
     * @param CMOrderRepositoryInterface $CMOrderRepository
     * @param CMOrderFactory $cmOrderFactory
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param OrderGetRequestFactory $orderGetRequestFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ApiClientInterface $apiClient,
        OrderInterfaceFactory $cmOrderFactory,
        CMOrderRepositoryInterface $cmOrderRepository,
        OrderRequestBuilderInterface $orderRequestBuilder,
        CMOrderInterfaceFactory $cmOrderInterfaceFactory,
        OrderGetRequestFactory $orderGetRequestFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->apiClient = $apiClient;
        $this->cmOrderFactory = $cmOrderFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->orderRequestBuilder = $orderRequestBuilder;
        $this->cmOrderInterfaceFactory = $cmOrderInterfaceFactory;
        $this->orderGetRequestFactory = $orderGetRequestFactory;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): CMOrderInterface
    {
        $order = $this->orderRepository->get($orderId);
        $orderCreateRequest = $this->orderRequestBuilder->create($order);

        $response = $this->apiClient->execute(
            $orderCreateRequest
        );

        if (empty($response['order_key'])) {
            throw new EmptyOrderKeyException(__('Empty order key'));
        }
        /** @var CMOrder $cmOrder */
        $model = $this->cmOrderFactory->create();
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
            'orderReference' => $orderCreateRequest->getPayload()['order_reference'],
            'orderKey' => $response['order_key'],
            'expiresOn' => $response['expires_on'],
        ]);
    }

    /**
     * @param CMOrder $cmOrder
     * @return array
     */
    public function get(CMOrder $cmOrder): array
    {
        /** @var OrderGetRequest $orderGetRequest */
        $orderGetRequest = $this->orderGetRequestFactory->create(['cmOrder' => $cmOrder]);

        return $this->apiClient->execute(
            $orderGetRequest
        );
    }

    /**
     * Update the order status if the order state is Order::STATE_PROCESSING
     *
     * @param Order $order
     * @param String $method
     * @param string|null $status
     */
    public function setOrderStatus(Order $order, string $method, ?string $status = null)
    {
        if (!isset($status)) {
            //TODO: Add the proper status
            $status = 'processing';
        }

        if (Order::STATE_PROCESSING === $order->getState()) {
            $order->addCommentToStatusHistory(__('Order processed by CM.'), $status);
        }
    }
}

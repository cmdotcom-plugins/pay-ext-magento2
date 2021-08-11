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
use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Api\OrderInterface as CMOrderClientInterface;
use CM\Payments\Exception\EmptyOrderKeyException;
use CM\Payments\Logger\CMPaymentsLogger;
use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
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
     * @var OrderItemsRequestBuilder
     */
    private $orderItemsRequestBuilder;

    /**
     * @var CMOrderInterfaceFactory
     */
    private $cmOrderInterfaceFactory;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * OrderService constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param CMOrderClientInterface $orderClient
     * @param CMOrderFactory $cmOrderFactory
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param OrderRequestBuilderInterface $orderRequestBuilder
     * @param OrderItemsRequestBuilderInterface $orderItemsRequestBuilder
     * @param CMOrderInterfaceFactory $cmOrderInterfaceFactory
     * @param ManagerInterface $eventManager
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CMOrderClientInterface $orderClient,
        CMOrderFactory $cmOrderFactory,
        CMOrderRepositoryInterface $cmOrderRepository,
        OrderRequestBuilderInterface $orderRequestBuilder,
        OrderItemsRequestBuilderInterface $orderItemsRequestBuilder,
        CMOrderInterfaceFactory $cmOrderInterfaceFactory,
        ManagerInterface $eventManager,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderClient = $orderClient;
        $this->cmOrderFactory = $cmOrderFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->orderRequestBuilder = $orderRequestBuilder;
        $this->orderItemsRequestBuilder = $orderItemsRequestBuilder;
        $this->cmOrderInterfaceFactory = $cmOrderInterfaceFactory;
        $this->eventManager = $eventManager;
        $this->logger = $cmPaymentsLogger;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId, bool $isItemsCreationNeeded = false): CMOrderInterface
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

        $this->eventManager->dispatch('cmpayments_before_order_create', [
            'order' => $order,
            'orderCreateRequest' => $orderCreateRequest,
        ]);

        $orderCreateResponse = $this->orderClient->create(
            $orderCreateRequest
        );

        if (empty($orderCreateResponse->getOrderKey())) {
            throw new EmptyOrderKeyException(__('Empty order key'));
        }

        if ($isItemsCreationNeeded) {
            $orderCreateItemsRequest = $this->orderItemsRequestBuilder->create(
                $orderCreateResponse->getOrderKey(),
                $order->getAllVisibleItems()
            );

            $this->logger->info(
                'CM Create order items request',
                [
                    'orderId' => $orderId,
                    'requestPayload' => $orderCreateItemsRequest->getPayload()
                ]
            );

            try {
                $this->eventManager->dispatch('cmpayments_before_order_items_create', [
                    'order' => $order,
                    'orderCreateItemsRequest' => $orderCreateItemsRequest
                ]);

                $this->orderClient->createItems($orderCreateItemsRequest);

                $this->eventManager->dispatch('cmpayments_after_order_items_create', [
                    'order' => $order
                ]);
            } catch (RequestException $exception) {
                $this->logger->info(
                    'CM Create order items request error',
                    [
                        'orderId' => $orderId,
                        'orderReference' => $orderCreateResponse->getOrderKey(),
                        'exceptionMessage' => $exception->getMessage()
                    ]
                );

                throw new LocalizedException(
                    __(
                        'The order items for order with ID "%1" and reference "%2" were not created properly.',
                        $orderId,
                        $orderCreateResponse->getOrderKey()
                    )
                );
            }
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

        $cmOrder = $this->cmOrderInterfaceFactory->create(
            [
                'url' => $orderCreateResponse->getUrl(),
                'orderReference' => $orderCreateRequest->getPayload()['order_reference'],
                'orderKey' => $orderCreateResponse->getOrderKey(),
                'expiresOn' => $orderCreateResponse->getExpiresOn()
            ]
        );

        $this->eventManager->dispatch('cmpayments_after_order_create', [
            'order' => $order,
            'cmOrder' => $cmOrder,
        ]);

        return $cmOrder;
    }
}

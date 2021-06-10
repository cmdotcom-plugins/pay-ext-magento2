<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Model\Data\OrderInterface as CMOrder;
use CM\Payments\Api\Model\Data\OrderInterfaceFactory as CMOrderFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Model\OrderCreate as OrderCreate;
use CM\Payments\Client\Model\OrderCreateFactory as OrderCreateFactory;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Client\Request\OrderCreateRequestFactory;
use CM\Payments\Client\Request\OrderGetRequest;
use CM\Payments\Client\Request\OrderGetRequestFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
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
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var OrderCreateFactory
     */
    private $orderCreateFactory;

    /**
     * @var OrderCreateRequestFactory
     */
    private $orderCreateRequestFactory;

    /**
     * @var OrderGetRequestFactory
     */
    private $orderGetRequestFactory;

    /**
     * OrderService constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param ApiClientInterface $apiClient
     * @param CMOrderFactory $cmOrderFactory
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param ConfigInterface $config
     * @param ResolverInterface $localeResolver
     * @param UrlInterface $urlBuilder
     * @param OrderCreateFactory $orderCreateFactory
     * @param OrderCreateRequestFactory $orderCreateRequestFactory
     * @param OrderGetRequestFactory $orderGetRequestFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ApiClientInterface $apiClient,
        CMOrderFactory $cmOrderFactory,
        CMOrderRepositoryInterface $cmOrderRepository,
        ConfigInterface $config,
        ResolverInterface $localeResolver,
        UrlInterface $urlBuilder,
        OrderCreateFactory $orderCreateFactory,
        OrderCreateRequestFactory $orderCreateRequestFactory,
        OrderGetRequestFactory $orderGetRequestFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->apiClient = $apiClient;
        $this->cmOrderFactory = $cmOrderFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->urlBuilder = $urlBuilder;
        $this->orderCreateFactory = $orderCreateFactory;
        $this->orderCreateRequestFactory = $orderCreateRequestFactory;
        $this->orderGetRequestFactory = $orderGetRequestFactory;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): string
    {
        $order = $this->orderRepository->get($orderId);

        /** @var OrderCreate $orderCreate */
        $orderCreate = $this->orderCreateFactory->create(
            [
                'orderId' => $order->getIncrementId(),
                'amount' => $this->getAmount($order),
                'currency' => $order->getStoreCurrencyCode(),
                'email' => $order->getBillingAddress()->getEmail(),
                // Todo: format locale and move to other file
                'language' => substr($this->localeResolver->emulate($order->getStoreId()), 0, 2),
                'country' => $order->getBillingAddress()->getCountryId(),
                'paymentProfile' => $this->config->getPaymentProfile($order->getStoreId()),
                'returnUrls' => [
                    'success' => $this->getReturnUrl($order->getIncrementId(), OrderCreate::STATUS_SUCCESS),
                    'pending' => $this->getReturnUrl($order->getIncrementId(), OrderCreate::STATUS_PENDING),
                    'cancelled' => $this->getReturnUrl($order->getIncrementId(), OrderCreate::STATUS_CANCELLED),
                    'error' => $this->getReturnUrl($order->getIncrementId(), OrderCreate::STATUS_ERROR)
                ]
            ]
        );

        /** @var OrderCreateRequest $orderCreateRequest */
        $orderCreateRequest = $this->orderCreateRequestFactory->create(['orderCreate' => $orderCreate]);
        $response = $this->apiClient->execute(
            $orderCreateRequest
        );

        if ($response['order_key']) {
            /** @var CMOrder $cmOrder */
            $cmOrder = $this->cmOrderFactory->create();
            $cmOrder->setOrderId((int)$orderId);
            $cmOrder->setOrderKey($response['order_key']);
            $cmOrder->setIncrementId($order->getIncrementId());

            $this->cmOrderRepository->save($cmOrder);
        }

        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        if ($response['expires_on']) {
            $additionalInformation['expires_at'] = $response['expires_on'];
        }

        if ($response['url']) {
            $additionalInformation['checkout_url'] = $response['url'];
        }

        $order->getPayment()->setAdditionalInformation($additionalInformation);
        $this->orderRepository->save($order);

        // Todo: return order domain object
        return $response['url'];
    }

    // Todo: move to helper method and convert money to int
    private function getAmount(OrderInterface $order): int
    {
        return (int)$order->getGrandTotal() * 100;
    }

    /**
     * Get Return Url
     *
     * @param string $orderReference
     * @param string $status
     * @return string
     */
    private function getReturnUrl(string $orderReference, string $status): string
    {
        return $this->urlBuilder->getUrl(
            'cmpayments/payment/result',
            [
                '_query' => [
                    'order_reference' => $orderReference,
                    'status' => $status
                ]
            ]
        );
    }

    /**
     * @param string $cmOrderId
     * @return array
     */
    public function get(string $cmOrderId): array
    {
        /** @var OrderGetRequest $orderGetRequest */
        $orderGetRequest = $this->orderGetRequestFactory->create()
            ->setEndpointParams(['{order_key}' => $cmOrderId]);

        $response = $this->apiClient->execute(
            $orderGetRequest
        );

        return $response;
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

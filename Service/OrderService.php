<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Model\Data\OrderInterfaceFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Model\Order;
use CM\Payments\Client\Model\OrderFactory;
use CM\Payments\Client\Request\OrderCreateRequest;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

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
    private $CMOrderRepository;
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
     * OrderService constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param ApiClientInterface $apiClient
     * @param OrderInterfaceFactory $orderFactory
     * @param CMOrderRepositoryInterface $CMOrderRepository
     * @param ConfigInterface $config
     * @param ResolverInterface $localeResolver
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ApiClientInterface $apiClient,
        OrderInterfaceFactory $orderFactory,
        CMOrderRepositoryInterface $CMOrderRepository,
        ConfigInterface $config,
        ResolverInterface $localeResolver,
        UrlInterface $urlBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->apiClient = $apiClient;
        $this->orderFactory = $orderFactory;
        $this->CMOrderRepository = $CMOrderRepository;
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): string
    {
        $order = $this->orderRepository->get($orderId);

        // Todo: create factory instead of using Order model directly
        $orderRequest = new Order(
            $order->getIncrementId(),
            $this->getAmount($order),
            $order->getStoreCurrencyCode(),
            $order->getBillingAddress()->getEmail(),
            // Todo: format locale and move to other file
            substr($this->localeResolver->emulate($order->getStoreId()), 0, 2),
            $order->getBillingAddress()->getCountryId(),
            $this->config->getPaymentProfile($order->getStoreId()),
            $this->urlBuilder
        );

        $response = $this->apiClient->execute(new OrderCreateRequest($orderRequest));

        if ($response['order_key']) {
            // Todo: save this cm_payments_order.
            $model = $this->orderFactory->create();
            $model->setOrderId((int)$orderId);
            $model->setOrderKey($response['order_key']);

            $this->CMOrderRepository->save($model);
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
}

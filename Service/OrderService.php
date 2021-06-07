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
use CM\Payments\Client\Model\Order as ClientOrder;
use CM\Payments\Client\Model\OrderFactory as ClientOrderFactory;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Client\Request\OrderCreateRequestFactory;
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
     * @var ClientOrderFactory
     */
    private $clientOrderFactory;
    /**
     * @var OrderCreateRequestFactory
     */
    private $orderCreateRequestFactory;

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
     * @param ClientOrderFactory $clientOrderFactory
     * @param OrderCreateRequestFactory $orderCreateRequestFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ApiClientInterface $apiClient,
        OrderInterfaceFactory $orderFactory,
        CMOrderRepositoryInterface $CMOrderRepository,
        ConfigInterface $config,
        ResolverInterface $localeResolver,
        UrlInterface $urlBuilder,
        ClientOrderFactory $clientOrderFactory,
        OrderCreateRequestFactory $orderCreateRequestFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->apiClient = $apiClient;
        $this->orderFactory = $orderFactory;
        $this->CMOrderRepository = $CMOrderRepository;
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->urlBuilder = $urlBuilder;
        $this->clientOrderFactory = $clientOrderFactory;
        $this->orderCreateRequestFactory = $orderCreateRequestFactory;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): string
    {
        $order = $this->orderRepository->get($orderId);

        /** @var ClientOrder $clientOrder */
        $clientOrder = $this->clientOrderFactory->create([
            'orderId' => $order->getIncrementId(),
            'amount' => $this->getAmount($order),
            'currency' => $order->getStoreCurrencyCode(),
            'email' => $order->getBillingAddress()->getEmail(),
            // Todo: format locale and move to other file
            'language' => substr($this->localeResolver->emulate($order->getStoreId()), 0, 2),
            'country' => $order->getBillingAddress()->getCountryId(),
            'paymentProfile' => $this->config->getPaymentProfile($order->getStoreId()),
            'returnUrls' => [
                'success' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_SUCCESS),
                'pending' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_PENDING),
                'cancelled' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_CANCELLED),
                'error' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_ERROR)
            ]
        ]);

        /** @var OrderCreateRequest $orderRequest */
        $orderRequest = $this->orderCreateRequestFactory->create(['order' => $clientOrder]);
        $response = $this->apiClient->execute(
            $orderRequest
        );

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

    /**
     * Get Return Url
     *
     * @param string $orderReference
     * @param string $status
     * @return string
     */
    private function getReturnUrl(string $orderReference, string $status): string
    {
        return $this->urlBuilder->getUrl('cmpayments/payment/result', [
            '_query' => [
                'order_reference' => $orderReference,
                'status' => $status
            ]
        ]);
    }
}

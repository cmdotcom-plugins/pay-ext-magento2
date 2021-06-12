<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Client\Model\Order as ClientOrder;
use CM\Payments\Client\Model\OrderFactory as ClientOrderFactory;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Client\Request\OrderCreateRequestFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderRequestBuilder implements OrderRequestBuilderInterface
{
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
     * OrderRequestBuilder constructor
     *
     * @param ConfigInterface $config
     * @param ResolverInterface $localeResolver
     * @param UrlInterface $urlBuilder
     * @param ClientOrderFactory $clientOrderFactory
     * @param OrderCreateRequestFactory $orderCreateRequestFactory
     */
    public function __construct(
        ConfigInterface $config,
        ResolverInterface $localeResolver,
        UrlInterface $urlBuilder,
        ClientOrderFactory $clientOrderFactory,
        OrderCreateRequestFactory $orderCreateRequestFactory
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->urlBuilder = $urlBuilder;
        $this->clientOrderFactory = $clientOrderFactory;
        $this->orderCreateRequestFactory = $orderCreateRequestFactory;
    }

    /**
     * @inheritDoc
     */
    public function create(OrderInterface $order): OrderCreateRequest
    {
        /** @var ClientOrder $clientOrder */
        $clientOrder = $this->clientOrderFactory->create([
            'orderId' => $order->getIncrementId(),
            'amount' => $this->getAmount($order),
            'currency' => $order->getOrderCurrencyCode(),
            'email' => $order->getBillingAddress()->getEmail(),
            'language' => $this->getLanguageCode((int)$order->getStoreId()),
            'country' => $order->getBillingAddress()->getCountryId(),
            'paymentProfile' => $this->config->getPaymentProfile($order->getStoreId()),
            'returnUrls' => [
                'success' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_SUCCESS),
                'pending' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_PENDING),
                'cancelled' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_CANCELLED),
                'error' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_ERROR)
            ]
        ]);

        return $this->orderCreateRequestFactory->create(['order' => $clientOrder]);
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function getLanguageCode(int $storeId): string
    {
        if (!$this->localeResolver->emulate($storeId)) {
            return '';
        }

        return substr($this->localeResolver->emulate($storeId), 0, 2);
    }

    /**
     * Converts amount to int
     * @param OrderInterface $order
     * @return int
     */
    private function getAmount(OrderInterface $order): int
    {
        return (int)($order->getGrandTotal() * 100);
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

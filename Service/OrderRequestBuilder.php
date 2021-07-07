<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Client\Model\OrderCreate as ClientOrder;
use CM\Payments\Client\Model\OrderCreateFactory as ClientOrderCreateFactory;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Client\Request\OrderCreateRequestFactory;
use CM\Payments\Model\ConfigProvider;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Math\Random as MathRandom;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
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
     * @var ClientOrderCreateFactory
     */
    private $clientOrderCreateFactory;

    /**
     * @var OrderCreateRequestFactory
     */
    private $orderCreateRequestFactory;

    /**
     * @var MathRandom
     */
    private $mathRandom;

    /**
     * OrderRequestBuilder constructor
     *
     * @param ConfigInterface $config
     * @param ResolverInterface $localeResolver
     * @param UrlInterface $urlBuilder
     * @param ClientOrderCreateFactory $clientOrderCreateFactory
     * @param OrderCreateRequestFactory $orderCreateRequestFactory
     * @param MathRandom $mathRandom
     */
    public function __construct(
        ConfigInterface $config,
        ResolverInterface $localeResolver,
        UrlInterface $urlBuilder,
        ClientOrderCreateFactory $clientOrderCreateFactory,
        OrderCreateRequestFactory $orderCreateRequestFactory,
        MathRandom $mathRandom
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->urlBuilder = $urlBuilder;
        $this->clientOrderCreateFactory = $clientOrderCreateFactory;
        $this->orderCreateRequestFactory = $orderCreateRequestFactory;
        $this->mathRandom = $mathRandom;
    }

    /**
     * @inheritDoc
     */
    public function create(OrderInterface $order): OrderCreateRequest
    {
        $paymentMethod = $order->getPayment()->getMethod();
        $paymentProfile = $this->config->getPaymentProfile();

        if ($paymentMethod == ConfigProvider::CODE_CREDIT_CARD) {
            $paymentProfile = $this->config->getCreditCardPaymentProfile() ?? $this->config->getPaymentProfile();
        } elseif ($paymentMethod == ConfigProvider::CODE_BANCONTACT) {
            $paymentProfile = $this->config->getBanContactPaymentProfile() ?? $this->config->getPaymentProfile();
        }

        /** @var ClientOrder $clientOrder */
        $clientOrder = $this->clientOrderCreateFactory->create([
            'orderId' => $order->getIncrementId(),
            'amount' => $this->getAmount($order),
            'currency' => $order->getOrderCurrencyCode(),
            'email' => $order->getShippingAddress()->getEmail(),
            'language' => $this->getLanguageCode((int)$order->getStoreId()),
            'country' => $order->getShippingAddress()->getCountryId(),
            'paymentProfile' => $paymentProfile ?: '',
            'returnUrls' => [
                'success' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_SUCCESS),
                'pending' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_PENDING),
                'cancelled' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_CANCELLED),
                'error' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_ERROR)
            ]
        ]);

        return $this->orderCreateRequestFactory->create(['orderCreate' => $clientOrder]);
    }

    /**
     * @inheritDoc
     */
    public function createByQuote(CartInterface $quote, bool $isEmptyProfile = false): OrderCreateRequest
    {
        $orderId = $this->mathRandom->getUniqueHash('Q_');

        /** @var ClientOrder $clientOrder */
        $clientOrder = $this->clientOrderCreateFactory->create([
            'orderId' => $orderId,
            'amount' => (int)($quote->getGrandTotal() * 100),
            'currency' => $quote->getCurrency()->getQuoteCurrencyCode(),
            'email' => $quote->getShippingAddress()->getEmail(),
            'language' => $this->getLanguageCode((int)$quote->getStoreId()),
            'country' => $quote->getShippingAddress()->getCountryId(),
            'paymentProfile' => $isEmptyProfile ? '' : $this->config->getPaymentProfile(),
            'returnUrls' => []
        ]);

        return $this->orderCreateRequestFactory->create(['orderCreate' => $clientOrder]);
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

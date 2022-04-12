<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Model\Response\PaymentMethod;
use CM\Payments\Exception\PaymentMethodNotFoundException;
use CM\Payments\Model\ConfigProvider;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\Data\CartInterface;

interface MethodServiceInterface
{
    public const CM_METHOD_IDEAL = 'IDEAL';
    /**
     * Methods List
     */
    public const METHODS = [
        ConfigProvider::CODE_CREDIT_CARD,
        ConfigProvider::CODE_MAESTRO,
        ConfigProvider::CODE_VPAY,
        ConfigProvider::CODE_IDEAL,
        ConfigProvider::CODE_PAYPAL,
        ConfigProvider::CODE_BANCONTACT,
        ConfigProvider::CODE_ELV,
        ConfigProvider::CODE_KLARNA,
        ConfigProvider::CODE_AFTERPAY,
        ConfigProvider::CODE_APPLEPAY,
        ConfigProvider::CODE_GIFTCARD
    ];

    /**
     * Mapping of CM methods to Magento
     */
    public const METHODS_MAPPING = [
        'VISA' => ConfigProvider::CODE_CREDIT_CARD,
        'MASTERCARD' => ConfigProvider::CODE_CREDIT_CARD,
        'MAESTRO' => ConfigProvider::CODE_MAESTRO,
        'V_PAY' => ConfigProvider::CODE_VPAY,
        MethodServiceInterface::CM_METHOD_IDEAL => ConfigProvider::CODE_IDEAL,
        'PAYPAL_EXPRESS_CHECKOUT' => ConfigProvider::CODE_PAYPAL,
        'BANCONTACT' => ConfigProvider::CODE_BANCONTACT,
        'ELV' => ConfigProvider::CODE_ELV,
        'KLARNA' => ConfigProvider::CODE_KLARNA,
        'AFTERPAY_OPEN_INVOICE' => ConfigProvider::CODE_AFTERPAY,
        'APPLE_PAY' => ConfigProvider::CODE_APPLEPAY,
        '(.*)GIFTCARD' => ConfigProvider::CODE_GIFTCARD,
    ];

    /**
     * Mapping of CM Credit Cards methods to Magento (by credit card type)
     */
    public const METHODS_CC_MAPPING = [
        'VI' => 'VISA',
        'MC' => 'MASTERCARD',
        'MD' => 'MAESTRO',
        'MI' => 'MAESTRO',
        'AE' => 'AMEX',
        'VP' => 'V_PAY'
    ];

    /**
     * Mapping of Magento Payment methods to CM Api Payment methods
     */
    public const API_METHODS_MAPPING = [
        ConfigProvider::CODE_IDEAL => 'IDEAL',
        ConfigProvider::CODE_PAYPAL => 'PAYPAL',
        ConfigProvider::CODE_ELV => 'ELV',
        ConfigProvider::CODE_KLARNA => 'KLARNA'
    ];

    /**
     * Get methods from CM and compare it with Magento methods
     * @param PaymentMethodInterface[] $magentoMethods
     * @param CartInterface $quote
     * @return PaymentMethodInterface[]
     */
    public function getMethodsByQuote(CartInterface $quote, array $magentoMethods): array;

    /**
     * @param string $orderKey
     * @return PaymentMethod[]
     */
    public function getCmMethods(string $orderKey): array;

    /**
     * @param PaymentMethodInterface[] $magentoMethods
     * @param PaymentMethod[] $cmMethods
     * @return PaymentMethodInterface[]
     */
    public function filterMethods(array $magentoMethods, array $cmMethods): array;

    /**
     * @param string $method
     * @param PaymentMethod[] $cmMethods
     * @return PaymentMethod
     *
     * @throws PaymentMethodNotFoundException
     */
    public function getMethodFromList(string $method, array $cmMethods): PaymentMethod;
}

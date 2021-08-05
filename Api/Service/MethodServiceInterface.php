<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Model\ConfigProvider;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Quote\Api\Data\CartInterface;

interface MethodServiceInterface
{
    /**
     * Methods List
     */
    public const METHODS = [
        ConfigProvider::CODE_CREDIT_CARD,
        ConfigProvider::CODE_IDEAL,
        ConfigProvider::CODE_PAYPAL,
        ConfigProvider::CODE_BANCONTACT,
        ConfigProvider::CODE_ELV,
        ConfigProvider::CODE_KLARNA
    ];

    /**
     * Mapping of CM methods to Magento
     */
    public const METHODS_MAPPING = [
        'VISA' => ConfigProvider::CODE_CREDIT_CARD,
        'MASTERCARD' => ConfigProvider::CODE_CREDIT_CARD,
        'MAESTRO' => ConfigProvider::CODE_CREDIT_CARD,
        'IDEAL' => ConfigProvider::CODE_IDEAL,
        'PAYPAL_EXPRESS_CHECKOUT' => ConfigProvider::CODE_PAYPAL,
        'BANCONTACT' => ConfigProvider::CODE_BANCONTACT,
        'ELV' => ConfigProvider::CODE_ELV,
        'KLARNA' => ConfigProvider::CODE_KLARNA
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
     * @param CartInterface $quote
     * @param PaymentDetailsInterface $paymentDetails
     * @return PaymentDetailsInterface
     */
    public function addMethodAdditionalData(
        CartInterface $quote,
        PaymentDetailsInterface $paymentDetails
    ): PaymentDetailsInterface;
}

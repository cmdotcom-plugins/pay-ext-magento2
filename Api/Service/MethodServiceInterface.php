<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Model\ConfigProvider;
use Magento\Quote\Api\Data\CartInterface;

interface MethodServiceInterface
{
    /**
     * Mapping of CM methods to Magento
     */
    public const METHODS_MAPPING = [
        'VISA' => ConfigProvider::CODE_CREDIT_CARD,
        'MASTERCARD' => ConfigProvider::CODE_CREDIT_CARD,
        'MAESTRO' => ConfigProvider::CODE_CREDIT_CARD,
        'IDEAL' => ConfigProvider::CODE_IDEAL,
        'PAYPAL_EXPRESS_CHECKOUT' => ConfigProvider::CODE_PAYPAL,
        'BANCONTACT' => ConfigProvider::CODE_BANCONTACT
    ];

    /**
     * Mapping of Magento Payment methods to CM Api Payment methods
     */
    public const API_METHODS_MAPPING = [
        ConfigProvider::CODE_IDEAL => 'IDEAL',
        ConfigProvider::CODE_PAYPAL => 'PAYPAL'
    ];

    /**
     * @param CartInterface $quote
     * @return array
     */
    public function getAvailablePaymentMethods(CartInterface $quote): array;
}

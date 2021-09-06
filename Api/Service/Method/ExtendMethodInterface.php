<?php

namespace CM\Payments\Api\Service\Method;

use CM\Payments\Client\Model\Response\PaymentMethod;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface;

interface ExtendMethodInterface
{
    /**
     * Extend CM.com payment methods with additional data
     *
     * @param string $paymentMethodCode
     * @param PaymentMethod $paymentMethod
     * @param PaymentDetailsExtensionInterface $paymentDetailsExtension
     *
     * @return PaymentDetailsExtensionInterface
     */
    public function extend(
        string $paymentMethodCode,
        PaymentMethod $paymentMethod,
        PaymentDetailsExtensionInterface
        $paymentDetailsExtension
    ): PaymentDetailsExtensionInterface;
}

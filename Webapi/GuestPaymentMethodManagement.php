<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Webapi;

use CM\Payments\Api\GuestPaymentMethodManagementInterface;
use CM\Payments\Api\PaymentMethodManagementInterface;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class GuestPaymentMethodManagement implements GuestPaymentMethodManagementInterface
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * GuestPaymentMethodManagement constructor
     *
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        PaymentMethodManagementInterface $paymentMethodManagement
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->paymentMethodManagement = $paymentMethodManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethods(
        string $cartId,
        AddressInterface $shippingAddress = null
    ): PaymentDetailsInterface {
        $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);

        return $this->paymentMethodManagement->getPaymentMethods($quoteId, $shippingAddress);
    }

    /**
     * {@inheritDoc}
     */
    public function getIbanIssuers(string $cartId): array
    {
        $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);

        return $this->paymentMethodManagement->getIbanIssuers($quoteId);
    }
}

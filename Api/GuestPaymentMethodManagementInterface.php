<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Interface GuestPaymentMethodManagementInterface
 *
 * @api
 */
interface GuestPaymentMethodManagementInterface
{
    /**
     * Get payment methods information
     *
     * @param string $cartId
     * @param AddressInterface|null $shippingAddress
     * @return PaymentDetailsInterface
     * @throws InputException
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function getPaymentMethods(string $cartId, ?AddressInterface $shippingAddress): PaymentDetailsInterface;
}

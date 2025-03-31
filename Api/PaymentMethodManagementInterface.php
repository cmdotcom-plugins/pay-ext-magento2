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
 * Interface PaymentMethodManagementInterface
 *
 * @api
 */
interface PaymentMethodManagementInterface
{
    /**
     * Get payment methods information
     *
     * @param int $cartId
     * @param AddressInterface|null $shippingAddress
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     * @throws InputException
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function getPaymentMethods(int $cartId, ?AddressInterface $shippingAddress): PaymentDetailsInterface;
}

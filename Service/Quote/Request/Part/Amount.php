<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByQuoteInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Quote\Api\Data\CartInterface;

class Amount implements RequestPartByQuoteInterface
{
    /**
     * @inheritDoc
     */
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate
    {
        $amountParts = [
            $quote->getShippingAddress()->getSubtotal(),
            $quote->getShippingAddress()->getShippingAmount(),
            $quote->getShippingAddress()->getTaxAmount(),
            $quote->getShippingAddress()->getDiscountAmount()
        ];
        $orderCreate->setAmount((int)round(array_sum($amountParts) * 100));

        return $orderCreate;
    }
}

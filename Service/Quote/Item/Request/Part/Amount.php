<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Item\Request\Part;

use CM\Payments\Api\Service\Order\Item\Request\RequestPartByQuoteItemInterface;
use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use Magento\Quote\Api\Data\CartItemInterface;

class Amount implements RequestPartByQuoteItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(CartItemInterface $quoteItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        $address = $quoteItem->getQuote()->getShippingAddress();
        if ($quoteItem->getQuote()->getIsVirtual()) {
            $address = $quoteItem->getQuote()->getBillingAddress();
        }
        if ($orderItemCreate->getSku() == OrderItemsRequestBuilderInterface::ITEM_SHIPPING_FEE_SKU) {
            $totalAmount = $address->getBaseShippingAmount()
                + $address->getBaseShippingTaxAmount()
                + $address->getBaseShippingDiscountTaxCompensationAmnt();
        } else {
            $totalAmount = $quoteItem->getBaseRowTotal()
                - $quoteItem->getBaseDiscountAmount()
                + $quoteItem->getBaseTaxAmount()
                + $quoteItem->getBaseDiscountTaxCompensationAmount();
        }

        $orderItemCreate->setAmount(
            (int)($totalAmount * 100)
        );

        return $orderItemCreate;
    }
}

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

class VatRate implements RequestPartByQuoteItemInterface
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

        $taxPercent = 0;
        if ($orderItemCreate->getSku() == OrderItemsRequestBuilderInterface::ITEM_SHIPPING_FEE_SKU) {
            if ($address->getBaseShippingAmount() > 0) {
                $taxPercent = ($address->getBaseShippingTaxAmount()
                        / $address->getBaseShippingAmount()) * 100;
            }
        } else {
            $taxPercent = $quoteItem->getTaxPercent();
        }

        $orderItemCreate->setVatRate(number_format((float)$taxPercent, 1));

        return $orderItemCreate;
    }
}

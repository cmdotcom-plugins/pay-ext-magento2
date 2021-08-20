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

class Type implements RequestPartByQuoteItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(CartItemInterface $quoteItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        if ($quoteItem->getSku() == OrderItemsRequestBuilderInterface::ITEM_SHIPPING_FEE_SKU) {
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_SHIPPING_FEE);
        } elseif ($quoteItem->getSku() == OrderItemsRequestBuilderInterface::ITEM_DISCOUNT_SKU) {
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_DISCOUNT);
        } elseif ($quoteItem->getIsVirtual()) {
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_DIGITAL);
        } else {
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_PHYSICAL);
        }

        return $orderItemCreate;
    }
}

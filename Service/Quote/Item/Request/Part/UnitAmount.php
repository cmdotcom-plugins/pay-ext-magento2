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

class UnitAmount implements RequestPartByQuoteItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(CartItemInterface $quoteItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        $totalAmount = $orderItemCreate->getAmount() / 100;
        $unitPrice = round($totalAmount / $quoteItem->getQty(), 2);

        if ($quoteItem->getSku() == OrderItemsRequestBuilderInterface::ITEM_SHIPPING_FEE_SKU) {
            $unitPrice = $totalAmount;
        }

        $orderItemCreate->setUnitAmount(
            (int)($unitPrice * 100)
        );

        return $orderItemCreate;
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Item\Request\Part;

use CM\Payments\Api\Service\Order\Item\Request\RequestPartByOrderItemInterface;
use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use Magento\Sales\Api\Data\OrderItemInterface;

class UnitAmount implements RequestPartByOrderItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderItemInterface $orderItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        $totalAmount = $orderItemCreate->getAmount() / 100;
        $discountAmount = abs($orderItem->getDiscountAmount() + $orderItem->getDiscountTaxCompensationAmount());
        $unitPrice = round(($totalAmount + $discountAmount) / $orderItem->getQtyOrdered(), 2);

        if ($orderItemCreate->getSku() == OrderItemsRequestBuilderInterface::ITEM_SHIPPING_FEE_SKU) {
            $unitPrice = $totalAmount;
        }

        $orderItemCreate->setUnitAmount(
            (int)($unitPrice * 100)
        );

        return $orderItemCreate;
    }
}

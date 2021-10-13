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

class Amount implements RequestPartByOrderItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderItemInterface $orderItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        if ($orderItem->getSku() == OrderItemsRequestBuilderInterface::ITEM_SHIPPING_FEE_SKU) {
            $totalAmount = $orderItem->getOrder()->getBaseShippingAmount()
                + $orderItem->getOrder()->getBaseShippingTaxAmount()
                + $orderItem->getOrder()->getBaseShippingDiscountTaxCompensationAmnt();
        } else {
            $totalAmount = $orderItem->getBaseRowTotal()
                - $orderItem->getBaseDiscountAmount()
                + $orderItem->getBaseTaxAmount()
                + $orderItem->getBaseDiscountTaxCompensationAmount();
        }

        $orderItemCreate->setAmount(
            (int)($totalAmount * 100)
        );

        return $orderItemCreate;
    }
}

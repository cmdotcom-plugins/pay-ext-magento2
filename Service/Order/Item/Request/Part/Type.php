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

class Type implements RequestPartByOrderItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderItemInterface $orderItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        if ($orderItem->getSku() == OrderItemsRequestBuilderInterface::ITEM_SHIPPING_FEE_SKU) {
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_SHIPPING_FEE);
        } elseif ($orderItem->getIsVirtual()) {
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_DIGITAL);
        } else {
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_PHYSICAL);
        }

        return $orderItemCreate;
    }
}

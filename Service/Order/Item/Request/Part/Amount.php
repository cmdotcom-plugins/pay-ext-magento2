<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Item\Request\Part;

use CM\Payments\Api\Service\Order\Item\Request\RequestPartByOrderItemInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use Magento\Sales\Api\Data\OrderItemInterface;

class Amount implements RequestPartByOrderItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderItemInterface $orderItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        $orderItemCreate->setAmount(
            (int)round(array_sum([
                    $orderItem->getRowTotal(),
                    $orderItem->getTaxAmount()
                ]) * 100)
        );

        return $orderItemCreate;
    }
}

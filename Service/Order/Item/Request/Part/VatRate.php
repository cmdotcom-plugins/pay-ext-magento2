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

class VatRate implements RequestPartByOrderItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderItemInterface $orderItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        $taxPercent = 0;
        if ($orderItem->getSku() == OrderItemsRequestBuilderInterface::ITEM_SHIPPING_FEE_SKU) {
            if ($orderItem->getOrder()->getBaseShippingAmount() > 0) {
                $taxPercent = ($orderItem->getOrder()->getBaseShippingTaxAmount()
                        / $orderItem->getOrder()->getBaseShippingAmount()) * 100;
            }
        } else {
            $taxPercent = $orderItem->getTaxPercent();
        }

        $orderItemCreate->setVatRate(number_format((float)$taxPercent, 1));

        return $orderItemCreate;
    }
}

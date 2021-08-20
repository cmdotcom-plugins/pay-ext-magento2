<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Item\Request\Part;

use CM\Payments\Api\Service\Order\Item\Request\RequestPartByQuoteItemInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use Magento\Quote\Api\Data\CartItemInterface;

class Description implements RequestPartByQuoteItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(CartItemInterface $quoteItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        $orderItemCreate->setDescription($quoteItem->getDescription());

        return $orderItemCreate;
    }
}

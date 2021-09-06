<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service\Order\Item\Request;

use CM\Payments\Client\Model\Request\OrderItemCreate;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartItemInterface;

interface RequestPartByQuoteItemInterface
{
    /**
     * @param CartItemInterface $quoteItem
     * @param OrderItemCreate $orderItemCreate
     * @return OrderItemCreate
     * @throws LocalizedException
     */
    public function process(CartItemInterface $quoteItem, OrderItemCreate $orderItemCreate): OrderItemCreate;
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service\Order\Request;

use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;

interface RequestPartByQuoteInterface
{
    /**
     * @param CartInterface $quote
     * @param OrderCreate $orderCreate
     * @return OrderCreate
     * @throws LocalizedException
     */
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate;
}

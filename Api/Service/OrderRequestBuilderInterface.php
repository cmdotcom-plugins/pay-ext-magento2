<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Request\OrderCreateRequest;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\LocalizedException;

interface OrderRequestBuilderInterface
{
    /**
     * @param OrderInterface $order
     * @return OrderCreateRequest
     * @throws LocalizedException
     */
    public function create(OrderInterface $order): OrderCreateRequest;

    /**
     * @param CartInterface $quote
     * @return OrderCreateRequest
     * @throws LocalizedException
     */
    public function createByQuote(CartInterface $quote): OrderCreateRequest;
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Api\Model\Domain\CMOrderInterface;
use CM\Payments\Exception\EmptyOrderKeyException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

interface OrderServiceInterface
{
    /**
     * @param int $orderId
     * @return CMOrderInterface
     * @throws EmptyOrderKeyException
     * @throws LocalizedException
     */
    public function create(int $orderId): CMOrderInterface;

    /**
     * @param CartInterface $quote
     * @return CMOrderInterface
     */
    public function createByQuote(CartInterface $quote): CMOrderInterface;

    /**
     * @param string $orderKey
     * @param OrderInterface $order
     * @return bool
     * @throws LocalizedException
     */
    public function createItems(string $orderKey, OrderInterface $order): bool;

    /**
     * @param CartInterface $quote
     * @param string $orderKey
     * @throws LocalizedException
     */
    public function createItemsByQuote(CartInterface $quote, string $orderKey): void;
}

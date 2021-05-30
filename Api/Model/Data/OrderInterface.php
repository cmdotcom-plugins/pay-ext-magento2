<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface OrderInterface extends ExtensibleDataInterface
{
    const ORDER_ID = 'order_id';
    const ORDER_KEY = 'order_key';
    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return OrderInterface
     */
    public function setOrderId(int $orderId): OrderInterface;

    /**
     * @return string
     */
    public function getOrderKey(): string;

    /**
     * @param string $orderKey
     * @return OrderInterface
     */
    public function setOrderKey(string $orderKey): OrderInterface;
}

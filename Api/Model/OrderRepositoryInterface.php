<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model;

use CM\Payments\Model\Order;
use CM\Payments\Api\Model\Data\OrderInterface;

interface OrderRepositoryInterface
{
    /**
     * @param string $orderKey
     * @return OrderInterface
     */
    public function getByOrderKey(string $orderKey): OrderInterface;

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function save(OrderInterface $order): OrderInterface;
}

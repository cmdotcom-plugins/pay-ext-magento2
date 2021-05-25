<?php
/**
 * Copyright © 2021 cm.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Model\Order;

interface OrderServiceInterface
{
    /**
     * @param string $orderId
     *
     * @return string
     */
    public function create(string $orderId): string;
}

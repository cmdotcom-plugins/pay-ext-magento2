<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model;

interface OrderRepositoryInterface
{
    public function getByOrderKey(string $orderKey);
}

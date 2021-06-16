<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Api\Model\Domain\CMOrderInterface;
use CM\Payments\Api\Model\Data\OrderInterface as CMOrder;

interface OrderServiceInterface
{
    /**
     * @param string $orderId
     *
     * @return CMOrderInterface
     */
    public function create(string $orderId): CMOrderInterface;

    /**
     * @param CMOrder $cmOrder
     * @return array
     */
    public function get(CMOrder $cmOrder): array;

    /**
     * @param string $orderKey
     * @return array
     */
    public function getAvailablePaymentMethods(string $orderKey): array;
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model;

use CM\Payments\Api\Model\Data\OrderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface OrderRepositoryInterface
{
    /**
     * @param string $incrementId
     * @return OrderInterface
     */
    public function getByIncrementId(string $incrementId): OrderInterface;

    /**
     * @param string $orderKey
     * @return OrderInterface
     *
     * @throws NoSuchEntityException
     */
    public function getByOrderKey(string $orderKey): OrderInterface;

    /**
     * Get Order by Order Id
     * @param int $orderId
     * @return OrderInterface
     *
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId): OrderInterface;

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function save(OrderInterface $order): OrderInterface;
}

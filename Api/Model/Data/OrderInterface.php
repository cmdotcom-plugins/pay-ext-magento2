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
    /**
     * Constant for table name
     */
    public const TABLE_NAME = 'cm_payments_order';

    /**
     * Properties
     */
    public const ID = 'id';
    public const ORDER_ID = 'order_id';
    public const ORDER_KEY = 'order_key';
    public const ORDER_INCREMENT_ID = 'increment_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

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

    /**
     * @return string
     */
    public function getIncrementId(): string;

    /**
     * @param string $incrementId
     * @return OrderInterface
     */
    public function setIncrementId(string $incrementId): OrderInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): ?string;

    /**
     * @param ?string $createdAt
     * @return OrderInterface
     */
    public function setCreatedAt(?string $createdAt): OrderInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param ?string $updatedAt
     * @return OrderInterface
     */
    public function setUpdatedAt(?string $updatedAt): OrderInterface;
}

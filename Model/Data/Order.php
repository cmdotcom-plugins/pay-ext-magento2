<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Data;

use CM\Payments\Api\Model\Data\OrderInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Order extends AbstractExtensibleModel implements OrderInterface
{
    /**
     * @inheritDoc
     */
    public function getOrderId(): int
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(int $orderId): OrderInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderKey(): string
    {
        return $this->getData(self::ORDER_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setOrderKey(string $orderKey): OrderInterface
    {
        return $this->setData(self::ORDER_KEY, $orderKey);
    }

    /**
     * @inheritDoc
     */
    public function getIncrementId(): string
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }
    
    /**
     * @inheritDoc
     */
    public function setIncrementId(string $incrementId): OrderInterface
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $incrementId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): OrderInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(?string $updatedAt): OrderInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

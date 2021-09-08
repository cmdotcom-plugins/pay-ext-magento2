<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Data;

use CM\Payments\Api\Model\Data\PaymentInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Payment extends AbstractExtensibleModel implements PaymentInterface
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
    public function setOrderId(int $orderId): PaymentInterface
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
    public function setOrderKey(string $orderKey): PaymentInterface
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
    public function setIncrementId(string $incrementId): PaymentInterface
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
    public function setCreatedAt(?string $createdAt): PaymentInterface
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
    public function setUpdatedAt(?string $updatedAt): PaymentInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentId(): string
    {
        return $this->getData(self::PAYMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentId(string $paymentId): PaymentInterface
    {
        return $this->setData(self::PAYMENT_ID, $paymentId);
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface PaymentInterface extends ExtensibleDataInterface
{
    /**
     * Constant for table name
     */
    public const TABLE_NAME = 'cm_payments_payment';

    /**
     * Properties
     */
    public const ID = 'id';
    public const ORDER_ID = 'order_id';
    public const ORDER_KEY = 'order_key';
    public const ORDER_INCREMENT_ID = 'increment_id';
    public const PAYMENT_ID = 'payment_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return PaymentInterface
     */
    public function setOrderId(int $orderId): PaymentInterface;

    /**
     * @return string
     */
    public function getOrderKey(): string;

    /**
     * @param string $orderKey
     * @return PaymentInterface
     */
    public function setOrderKey(string $orderKey): PaymentInterface;

    /**
     * @return string
     */
    public function getIncrementId(): string;

    /**
     * @return string
     */
    public function getPaymentId(): string;

    /**
     * @param string $paymentId
     * @return PaymentInterface
     */
    public function setPaymentId(string $paymentId): PaymentInterface;

    /**
     * @param string $incrementId
     * @return PaymentInterface
     */
    public function setIncrementId(string $incrementId): PaymentInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): ?string;

    /**
     * @param ?string $createdAt
     * @return PaymentInterface
     */
    public function setCreatedAt(?string $createdAt): PaymentInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param ?string $updatedAt
     * @return PaymentInterface
     */
    public function setUpdatedAt(?string $updatedAt): PaymentInterface;
}

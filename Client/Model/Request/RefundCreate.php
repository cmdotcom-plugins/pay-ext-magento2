<?php

/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Request;

class RefundCreate
{
    /**
     * @var string
     */
    private $refundReference;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $refundRequiredDate;
    /**
     * @var string
     */
    private $orderKey;
    /**
     * @var string
     */
    private $paymentId;
    /**
     * @var string
     */
    private $orderId;

    /**
     * Order constructor
     *
     * @param string $method
     * @param array $idealDetails
     */
    public function __construct(
        string $orderKey,
        string $paymentId,
        string $orderId,
        string $description,
        int $amount,
        string $currency,
        string $refundReference = '',
        string $refundRequiredDate = ''
    ) {
        $this->description = $description;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->refundReference = $refundReference;
        $this->refundRequiredDate = $refundRequiredDate;
        $this->orderKey = $orderKey;
        $this->paymentId = $paymentId;
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getRefundReference(): string
    {
        return $this->refundReference;
    }

    /**
     * @param string $refundReference
     * @return RefundCreate
     */
    public function setRefundReference(string $refundReference): RefundCreate
    {
        $this->refundReference = $refundReference;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return RefundCreate
     */
    public function setDescription(string $description): RefundCreate
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return RefundCreate
     */
    public function setAmount(int $amount): RefundCreate
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return RefundCreate
     */
    public function setCurrency(string $currency): RefundCreate
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefundRequiredDate(): string
    {
        return $this->refundRequiredDate;
    }

    /**
     * @param string $refundRequiredDate
     * @return RefundCreate
     */
    public function setRefundRequiredDate(string $refundRequiredDate): RefundCreate
    {
        $this->refundRequiredDate = $refundRequiredDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @param string $paymentId
     */
    public function setPaymentId(string $paymentId): RefundCreate
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderKey(): string
    {
        return $this->orderKey;
    }

    /**
     * @param string $orderKey
     */
    public function setOrderKey(string $orderKey): RefundCreate
    {
        $this->orderKey = $orderKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): RefundCreate
    {
        $this->orderId = $orderId;
        return $this;
    }
}

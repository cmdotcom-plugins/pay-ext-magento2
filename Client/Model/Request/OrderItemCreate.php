<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Request;

class OrderItemCreate
{
    /**
     * @var int
     */
    private $itemId;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $unitAmount;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var int
     */
    private $vatAmount;

    /**
     * @var string
     */
    private $vatRate;

    /**
     * OrderItemCreate constructor
     *
     * @param ?int $itemId
     * @param ?string $sku
     * @param ?string $name
     * @param ?string $description
     * @param ?string $type
     * @param ?int $quantity
     * @param ?string $currency
     * @param ?int $unitAmount
     * @param ?int $amount
     * @param ?int $vatAmount
     * @param ?string $vatRate
     */
    public function __construct(
        ?int $itemId = null,
        ?string $sku = null,
        ?string $name = null,
        ?string $description = null,
        ?string $type = null,
        ?int $quantity = null,
        ?string $currency = null,
        ?int $unitAmount = null,
        ?int $amount = null,
        ?int $vatAmount = null,
        ?string $vatRate = null
    ) {
        $this->itemId = $itemId;
        $this->sku = $sku;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->quantity = $quantity;
        $this->currency = $currency;
        $this->unitAmount = $unitAmount;
        $this->amount = $amount;
        $this->vatAmount = $vatAmount;
        $this->vatRate = $vatRate;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'number' => $this->itemId,
            'code' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'currency' => $this->currency,
            'unit_amount' => $this->unitAmount,
            'amount' => $this->amount,
            'vat_amount' => $this->vatAmount,
            'vat_rate' => $this->vatRate
        ], function ($v, $k) {
            return $v !== '';
        },
        ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @return ?int
     */
    public function getItemId(): ?int
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     */
    public function setItemId(int $itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return ?string
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->sku;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param ?string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return ?string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return ?string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getUnitAmount(): ?int
    {
        return $this->unitAmount;
    }

    /**
     * @param int $unitAmount
     */
    public function setUnitAmount(int $unitAmount): void
    {
        $this->unitAmount = $unitAmount;
    }

    /**
     * @return ?int
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getVatAmount(): ?int
    {
        return $this->vatAmount;
    }

    /**
     * @param int $vatAmount
     */
    public function setVatAmount(int $vatAmount): void
    {
        $this->vatAmount = $vatAmount;
    }

    /**
     * @return ?string
     */
    public function getVatRate(): ?string
    {
        return $this->vatRate;
    }

    /**
     * @param string $vatRate
     */
    public function setVatRate(string $vatRate): void
    {
        $this->vatRate = $vatRate;
    }
}

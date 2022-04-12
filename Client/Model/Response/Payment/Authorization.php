<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response\Payment;

class Authorization
{
    public const STATE_AUTHORIZED = 'AUTHORIZED';
    public const STATE_CANCELED = 'CANCELED';

    /**
     * @var int|null
     */
    private $amount;

    /**
     * @var string|null
     */
    private $currency;

    /**
     * @var string|null
     */
    private $confidence;

    /**
     * @var string|null
     */
    private $state;

    /**
     * @var string|null
     */
    private $reason;

    /**
     * Authorization constructor.
     * @param array $authorization
     */
    public function __construct(
        array $authorization
    ) {
        $this->amount = $authorization['amount'] ?? null;
        $this->currency = $authorization['currency'] ?? null;
        $this->confidence = $authorization['confidence'] ?? null;
        $this->state = $authorization['state'] ?? null;
        $this->reason = $authorization['reason'] ?? null;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getConfidence(): string
    {
        return $this->confidence;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }
}

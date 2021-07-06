<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response\Payment;

class Authorization
{
    const STATE_AUTHORIZED = 'AUTHORIZED';

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
    private $confidence;

    /**
     * @var string
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
        $this->amount = $authorization['amount'];
        $this->currency = $authorization['currency'];
        $this->confidence = $authorization['confidence'];
        $this->state = $authorization['state'];
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

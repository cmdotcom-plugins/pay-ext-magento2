<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

use CM\Payments\Client\Model\Response\Payment\Payment;

interface OrderDetailInterface
{
    public const LEVEL_SAFE = 'SAFE';

    /**
     * @return bool
     */
    public function isSafe(): bool;

    /**
     * @return Payment|null
     */
    public function getAuthorizedPayment(): ?Payment;

    /**
     * @return string
     */
    public function getOrderReference(): string;

    /**
     * @return string
     */
    public function getExpiresOn(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return int
     */
    public function getAmount(): int;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @return string
     */
    public function getLanguage(): string;

    /**
     * @return string
     */
    public function getCountry(): string;

    /**
     * @return string
     */
    public function getProfile(): string;

    /**
     * @return string
     */
    public function getTimestamp(): string;

    /**
     * @return array
     */
    public function getConsideredSafe(): array;

    /**
     * @return array
     */
    public function getPayments(): array;
}

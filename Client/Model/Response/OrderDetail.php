<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response;

use CM\Payments\Client\Api\OrderDetailInterface;
use CM\Payments\Client\Model\Response\Payment\Authorization;
use CM\Payments\Client\Model\Response\Payment\Payment;

class OrderDetail implements OrderDetailInterface
{
    private string $orderReference;
    private string $description;
    private int $amount;
    private string $email;
    private string $language;
    private string $country;
    private string $profile;
    private string $timestamp;
    private string $expires_on;
    private ?array $consideredSafe;
    /**
     * @var Payment[]
     */
    private $payments;

    /**
     * OrderDetail constructor.
     * @param array $orderDetail
     */
    public function __construct(
        array $orderDetail
    ) {
        $this->orderReference = $orderDetail['order_reference'];
        $this->description = $orderDetail['description'];
        $this->amount = $orderDetail['amount'];
        ;
        $this->email = $orderDetail['email'];
        ;
        $this->language = $orderDetail['language'];
        $this->country = $orderDetail['country'];
        $this->profile = $orderDetail['profile'];
        $this->timestamp = $orderDetail['timestamp'];
        $this->expires_on = $orderDetail['expires_on'];
        $this->consideredSafe = $orderDetail['considered_safe'] ?? [];
        $this->payments = isset($orderDetail['payments']) ? $this->mapPayments($orderDetail['payments']) : [];
    }

    /**
     * @inheritDoc
     */
    public function isSafe(): bool
    {
        return isset($this->consideredSafe['level']) && $this->consideredSafe['level'] === OrderDetail::LEVEL_SAFE;
    }

    /**
     * @inheritDoc
     */
    public function getOrderReference(): string
    {
        return $this->orderReference;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresOn(): string
    {
        return $this->expires_on;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @inheritDoc
     */
    public function getProfile(): string
    {
        return $this->profile;
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @inheritDoc
     */
    public function getConsideredSafe(): array
    {
        return $this->consideredSafe;
    }

    /**
     * @return Payment[]
     */
    public function getPayments(): array
    {
        return $this->payments;
    }

    /**
     * @return Payment|null
     */
    public function getAuthorizedPayment(): ?Payment
    {
        foreach ($this->getPayments() as $payment) {
            if ($payment->getAuthorization()
                && $payment->getAuthorization()->getState() === Authorization::STATE_AUTHORIZED
            ) {
                return $payment;
            }
        }

        return null;
    }

    /**
     * @param array $payments
     * @return Payment[]
     */
    private function mapPayments(array $payments): array
    {
        return array_map(function ($payment) {
            return new Payment($payment);
        }, $payments);
    }
}

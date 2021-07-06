<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model;

use CM\Payments\Api\Model\Data\PaymentInterface;

interface PaymentRepositoryInterface
{
    /**
     * @param PaymentInterface $payment
     * @return PaymentInterface
     */
    public function save(PaymentInterface $payment): PaymentInterface;

    /**
     * @param string $orderKey
     * @return PaymentInterface
     */
    public function getByOrderKey(string $orderKey): PaymentInterface;

    /**
     * @param string $paymentId
     * @return PaymentInterface
     */
    public function getByPaymentId(string $paymentId): PaymentInterface;
}

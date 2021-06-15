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
}

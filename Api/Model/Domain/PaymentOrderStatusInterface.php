<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model\Domain;

interface PaymentOrderStatusInterface
{
    /**
     * @return string
     */
    public function getOrderId(): string;

    /**
     * @return string
     */
    public function getStatus(): string;
}

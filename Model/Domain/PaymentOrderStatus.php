<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Domain;

use CM\Payments\Api\Model\Domain\PaymentOrderStatusInterface;

class PaymentOrderStatus implements PaymentOrderStatusInterface
{
    /**
     * @var string
     */
    private $orderId;
    /**
     * @var string
     */
    private $status;

    /**
     * PaymentOrderStatus constructor.
     * @param string $orderId
     * @param string $status
     */
    public function __construct(string $orderId, string $status)
    {
        $this->orderId = $orderId;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}

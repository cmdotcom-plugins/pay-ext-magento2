<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Request\PaymentCaptureCreateRequest;
use Magento\Sales\Api\Data\OrderInterface;

interface PaymentCaptureRequestBuilderInterface
{
    /**
     * Create Payment capture request
     *
     * @param string $orderKey
     * @param string $paymentId
     * @param OrderInterface $order
     *
     * @return PaymentCaptureCreateRequest
     */
    public function create(
        string $orderKey,
        string $paymentId,
        OrderInterface $order
    ): PaymentCaptureCreateRequest;
}

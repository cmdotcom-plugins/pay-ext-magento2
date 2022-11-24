<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Api\Service\Payment\Capture\Request;

use CM\Payments\Client\Model\Request\PaymentCaptureCreate;
use Magento\Sales\Api\Data\OrderInterface;
use CM\Payments\Client\Model\Request\PaymentCreate;

interface RequestPartInterface
{
    /**
     * Process the Capture request parts
     *
     * @param PaymentCaptureCreate $paymentCaptureCreate
     * @param OrderInterface|null $order
     *
     * @return PaymentCreate
     */
    public function process(
        PaymentCaptureCreate $paymentCaptureCreate,
        OrderInterface $order
    ): PaymentCaptureCreate;
}

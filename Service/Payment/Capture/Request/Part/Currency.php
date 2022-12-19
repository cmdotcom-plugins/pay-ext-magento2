<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Service\Payment\Capture\Request\Part;

use CM\Payments\Api\Service\Payment\Capture\Request\RequestPartInterface;
use CM\Payments\Client\Model\Request\PaymentCaptureCreate;
use Magento\Sales\Api\Data\OrderInterface;

class Currency implements RequestPartInterface
{
    /**
     * @inheritDoc
     */
    public function process(
        PaymentCaptureCreate $paymentCaptureCreate,
        OrderInterface $order
    ): PaymentCaptureCreate {
        $paymentCaptureCreate->setCurrency($order->getBaseCurrencyCode());

        return $paymentCaptureCreate;
    }
}

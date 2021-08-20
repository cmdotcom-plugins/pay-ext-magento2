<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Service\Payment\Request\Part;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\Payment\Request\RequestPartInterface;
use CM\Payments\Client\Model\Request\PaymentCreate;
use Magento\Sales\Api\Data\OrderInterface;

class Method implements RequestPartInterface
{
    /**
     * @inheritDoc
     */
    public function process(
        OrderInterface $order = null,
        CardDetailsInterface $cardDetails = null,
        BrowserDetailsInterface $browserDetails = null,
        PaymentCreate $paymentCreate
    ): PaymentCreate {
        $paymentCreate->setMethod($this->getMethod($order));

        return $paymentCreate;
    }

    /**
     * @inheritDoc
     */
    public function needsOrder(): bool
    {
        return true;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function getMethod(OrderInterface $order): string
    {
        return MethodServiceInterface::API_METHODS_MAPPING[$order->getPayment()->getMethod()]
            ?? $order->getPayment()->getMethod();
    }
}

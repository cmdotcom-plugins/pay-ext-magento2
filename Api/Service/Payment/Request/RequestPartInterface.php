<?php

namespace CM\Payments\Api\Service\Payment\Request;

use Magento\Sales\Api\Data\OrderInterface;
use CM\Payments\Client\Model\Request\PaymentCreate;

interface RequestPartInterface
{
    /**
     * @param OrderInterface $order
     * @param PaymentCreate $paymentCreate
     *
     * @return PaymentCreate
     */
    public function process(OrderInterface $order, PaymentCreate $paymentCreate): PaymentCreate;
}

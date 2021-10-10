<?php

namespace CM\Payments\Api\Service\Payment\Request;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use Magento\Sales\Api\Data\OrderInterface;
use CM\Payments\Client\Model\Request\PaymentCreate;

interface RequestPartInterface
{
    /**
     * @param OrderInterface|null $order
     * @param CardDetailsInterface|null $cardDetails
     * @param BrowserDetailsInterface|null $browserDetails
     * @param PaymentCreate $paymentCreate
     *
     * @return PaymentCreate
     */
    public function process(
        PaymentCreate $paymentCreate,
        OrderInterface $order = null,
        CardDetailsInterface $cardDetails = null,
        BrowserDetailsInterface $browserDetails = null
    ): PaymentCreate;

    /**
     * Determine if the request parts needs the order object
     *
     * @return bool
     */
    public function needsOrder(): bool;
}

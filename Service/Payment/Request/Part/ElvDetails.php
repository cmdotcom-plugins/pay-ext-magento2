<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Service\Payment\Request\Part;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use CM\Payments\Api\Service\Payment\Request\RequestPartInterface;
use CM\Payments\Client\Model\Request\PaymentCreate;
use CM\Payments\Model\ConfigProvider;
use Magento\Sales\Api\Data\OrderInterface;

class ElvDetails implements RequestPartInterface
{
    /**
     * @inheritDoc
     */
    public function process(
        PaymentCreate $paymentCreate,
        ?OrderInterface $order = null,
        ?CardDetailsInterface $cardDetails = null,
        ?BrowserDetailsInterface $browserDetails = null
    ): PaymentCreate {
        if ($order->getPayment()->getMethod() !== ConfigProvider::CODE_ELV) {
            return $paymentCreate;
        }

        $value = $this->getElvPaymentData($order);
        $paymentCreate->setElvDetails(
            [
                'iban' => $value
            ]
        );

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
    private function getElvPaymentData(OrderInterface $order): string
    {
        $additionalData = $order->getPayment()->getAdditionalInformation();

        if (isset($additionalData['iban'])) {
            return $additionalData['iban'];
        }

        return '';
    }
}

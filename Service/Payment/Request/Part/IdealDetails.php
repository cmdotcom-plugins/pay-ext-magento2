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

class IdealDetails implements RequestPartInterface
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
        if ($order->getPayment()->getMethod() !== ConfigProvider::CODE_IDEAL) {
            return $paymentCreate;
        }

        $value = $this->getSelectedIssuer($order);
        $paymentCreate->setIdealDetails([
            'issuer_id' => $value
        ]);

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
    private function getSelectedIssuer(OrderInterface $order): string
    {
        $additionalData = $order->getPayment()->getAdditionalInformation();

        if (isset($additionalData['selected_issuer'])) {
            return $additionalData['selected_issuer'];
        }

        return '';
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Service\Payment\Request\Part;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use CM\Payments\Api\Service\Payment\Request\RequestPartInterface;
use CM\Payments\Client\Model\Request\BrowserInformation;
use CM\Payments\Client\Model\Request\EncryptedCardDetails;
use CM\Payments\Client\Model\Request\PaymentCreate;
use Magento\Sales\Api\Data\OrderInterface;
use CM\Payments\Client\Model\Request\CardDetails as CardDetailsModel;

class CardDetails implements RequestPartInterface
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
        if ($cardDetails === null || $browserDetails === null) {
            return $paymentCreate;
        }

        $paymentCreate->setMethod($cardDetails->getMethod());

        $browserInformation = new BrowserInformation(
            $browserDetails->getShopperIp(),
            $browserDetails->getAccept(),
            $browserDetails->getUserAgent()
        );
        $encryptedCardDetails = new EncryptedCardDetails($cardDetails->getEncryptedCardData());
        $paymentCardDetails = new CardDetailsModel($browserInformation, $encryptedCardDetails);
        $paymentCreate->setCardDetails($paymentCardDetails);

        return $paymentCreate;
    }

    /**
     * @inheritDoc
     */
    public function needsOrder(): bool
    {
        return false;
    }
}

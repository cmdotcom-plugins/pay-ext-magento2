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
use CM\Payments\Client\Model\Request\BrowserInformation;
use CM\Payments\Client\Model\Request\CardDetails as CardDetailsModel;
use CM\Payments\Client\Model\Request\EncryptedCardDetails;
use CM\Payments\Client\Model\Request\PaymentCreate;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\OrderInterface;

class CardDetails implements RequestPartInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * CardDetails constructor
     *
     * @param Request $request
     * @param StringUtils $stringUtils
     */
    public function __construct(
        Request $request,
        StringUtils $stringUtils
    ) {
        $this->request = $request;
        $this->stringUtils = $stringUtils;
    }

    /**
     * @inheritDoc
     */
    public function process(
        PaymentCreate $paymentCreate,
        ?OrderInterface $order = null,
        ?CardDetailsInterface $cardDetails = null,
        ?BrowserDetailsInterface $browserDetails = null
    ): PaymentCreate {
        if ($cardDetails === null || $browserDetails === null) {
            return $paymentCreate;
        }

        $paymentCreate->setMethod(MethodServiceInterface::METHODS_CC_MAPPING[$cardDetails->getMethod()]);

        $browserInformation = new BrowserInformation(
            $browserDetails->getShopperIp() ?? $this->getShopperIp(),
            $browserDetails->getAccept() ?? $this->getAccept(),
            $browserDetails->getUserAgent() ?? $this->getUserAgent()
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

    /**
     * Retrieve Shopper IP
     *
     * @return string
     */
    private function getShopperIp(): string
    {
        return $this->request->getClientIp();
    }

    /**
     * Retrieve Accept
     *
     * @return string
     */
    private function getAccept(): string
    {
        return $this->stringUtils->cleanString(
            $this->request->getServerValue('HTTP_ACCEPT')
        );
    }

    /**
     * Retrieve User Agent
     *
     * @return string
     */
    private function getUserAgent(): string
    {
        return $this->stringUtils->cleanString(
            $this->request->getServerValue('HTTP_USER_AGENT')
        );
    }
}

<?php

/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Request;

class CardDetails
{
    /**
     * @var BrowserInformation
     */
    private $browserInformation;

    /**
     * @var EncryptedCardDetails
     */
    private $encryptedCardDetails;

    /**
     * @param BrowserInformation $browserInformation
     * @param EncryptedCardDetails $encryptedCardDetails
     */
    public function __construct(
        BrowserInformation $browserInformation,
        EncryptedCardDetails $encryptedCardDetails
    ) {
        $this->browserInformation = $browserInformation;
        $this->encryptedCardDetails = $encryptedCardDetails;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'browser_information' => $this->browserInformation->toArray(),
            'encrypted_card_details' => $this->encryptedCardDetails->toArray()
        ];
    }
    /**
     * @return BrowserInformation
     */
    public function getBrowserInformation(): BrowserInformation
    {
        return $this->browserInformation;
    }

    /**
     * @param BrowserInformation $browserInformation
     * @return CardDetails
     */
    public function setBrowserInformation(BrowserInformation $browserInformation): CardDetails
    {
        $this->browserInformation = $browserInformation;
        return $this;
    }

    /**
     * @return EncryptedCardDetails
     */
    public function getEncryptedCardDetails(): EncryptedCardDetails
    {
        return $this->encryptedCardDetails;
    }

    /**
     * @param EncryptedCardDetails $encryptedCardDetails
     * @return CardDetails
     */
    public function setEncryptedCardDetails(EncryptedCardDetails $encryptedCardDetails): CardDetails
    {
        $this->encryptedCardDetails = $encryptedCardDetails;
        return $this;
    }
}

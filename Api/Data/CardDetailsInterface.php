<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Data;

/**
 * Interface CardDetailsInterface
 *
 * @api
 */
interface CardDetailsInterface
{
    /**
     * Properties
     */
    public const METHOD = 'method';
    public const ENCRYPTED_CARD_DATA = 'encrypted_card_data';

    /**
     * Get option method
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Set option method
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method): CardDetailsInterface;

    /**
     * Get option encryptedCardData
     *
     * @return string
     */
    public function getEncryptedCardData(): string;

    /**
     * Set option encryptedCardData
     *
     * @param string $cardData
     * @return $this
     */
    public function setEncryptedCardData(string $cardData): CardDetailsInterface;
}

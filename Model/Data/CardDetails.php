<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Data;

use CM\Payments\Api\Data\CardDetailsInterface;
use Magento\Framework\DataObject;

class CardDetails extends DataObject implements CardDetailsInterface
{

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->_getData(self::METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setMethod($method): CardDetailsInterface
    {
        $this->setData(self::METHOD, $method);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEncryptedCardData(): string
    {
        return $this->_getData(self::ENCRYPTED_CARD_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setEncryptedCardData(string $cardData): CardDetailsInterface
    {
        $this->setData(self::ENCRYPTED_CARD_DATA, $cardData);

        return $this;
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Data;

use CM\Payments\Api\Model\Data\IssuerInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Issuer extends AbstractExtensibleModel implements IssuerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): IssuerInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title): IssuerInterface
    {
        return $this->setData(self::TITLE, $title);
    }
}

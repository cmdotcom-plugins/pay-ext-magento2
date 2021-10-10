<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Data;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use Magento\Framework\DataObject;

class BrowserDetails extends DataObject implements BrowserDetailsInterface
{
    /**
     * @inheritDoc
     */
    public function getShopperIp(): ?string
    {
        return $this->_getData(self::SHOPPER_IP);
    }

    /**
     * @inheritDoc
     */
    public function setShopperIp($shopperIp): BrowserDetailsInterface
    {
        $this->setData(self::SHOPPER_IP, $shopperIp);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAccept(): ?string
    {
        return $this->_getData(self::ACCEPT);
    }

    /**
     * @inheritDoc
     */
    public function setAccept(string $accept): BrowserDetailsInterface
    {
        $this->setData(self::ACCEPT, $accept);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserAgent(): ?string
    {
        return $this->_getData(self::USER_AGENT);
    }

    /**
     * @inheritDoc
     */
    public function setUserAgent(string $userAgent): BrowserDetailsInterface
    {
        $this->setData(self::USER_AGENT, $userAgent);

        return $this;
    }
}

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
    public function getShopperIp()
    {
        return $this->_getData(self::SHOPPER_IP);
    }

    /**
     * @inheritDoc
     */
    public function setShopperIp($shopperIp)
    {
        $this->setData(self::SHOPPER_IP, $shopperIp);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAccept()
    {
        return $this->_getData(self::ACCEPT);
    }

    /**
     * @inheritDoc
     */
    public function setAccept(string $accept)
    {
        $this->setData(self::ACCEPT, $accept);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserAgent()
    {
        return $this->_getData(self::USER_AGENT);
    }

    /**
     * @inheritDoc
     */
    public function setUserAgent(string $userAgent)
    {
        $this->setData(self::USER_AGENT, $userAgent);

        return $this;
    }
}

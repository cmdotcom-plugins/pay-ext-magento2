<?php

/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Request;

class BrowserInformation
{
    /**
     * @var string
     */
    private $shopperIp;

    /**
     * @var string
     */
    private $accept;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * @param string $shopperIp
     * @param string $accept
     * @param string $userAgent
     */
    public function __construct(
        string $shopperIp,
        string $accept,
        string $userAgent
    ) {
        $this->shopperIp = $shopperIp;
        $this->accept = $accept;
        $this->userAgent = $userAgent;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'shopper_ip' => $this->shopperIp,
            'accept' => $this->accept,
            'user_agent' => $this->userAgent
        ];
    }
    /**
     * @return string|null
     */
    public function getShopperIp(): ?string
    {
        return $this->shopperIp;
    }

    /**
     * @return string|null
     */
    public function getAccept(): ?string
    {
        return $this->accept;
    }

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @param string $shopperIp
     * @return BrowserInformation
     */
    public function setShopperIp(string $shopperIp): BrowserInformation
    {
        $this->shopperIp = $shopperIp;
        return $this;
    }

    /**
     * @param string $accept
     * @return BrowserInformation
     */
    public function setAccept(string $accept): BrowserInformation
    {
        $this->accept = $accept;
        return $this;
    }

    /**
     * @param string $userAgent
     * @return BrowserInformation
     */
    public function setUserAgent(string $userAgent): BrowserInformation
    {
        $this->userAgent = $userAgent;
        return $this;
    }
}

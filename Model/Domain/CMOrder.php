<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Model\Domain;

use CM\Payments\Api\Model\Domain\CMOrderInterface;

class CMOrder implements CMOrderInterface
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $orderReference;
    /**
     * @var string
     */
    private $orderKey;
    /**
     * @var string
     */
    private $expiresOn;

    /**
     * CMOrder constructor.
     * @param string $url
     * @param string $orderReference
     * @param string $orderKey
     * @param string $expiresOn
     */
    public function __construct(string $url, string $orderReference, string $orderKey, string $expiresOn)
    {
        $this->url = $url;
        $this->orderReference = $orderReference;
        $this->orderKey = $orderKey;
        $this->expiresOn = $expiresOn;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getExpiresOn(): string
    {
        return $this->expiresOn;
    }

    /**
     * @return string
     */
    public function getOrderReference(): string
    {
        return $this->orderReference;
    }

    /**
     * @return string
     */
    public function getOrderKey(): string
    {
        return $this->orderKey;
    }
}

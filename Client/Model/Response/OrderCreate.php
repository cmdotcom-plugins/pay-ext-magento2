<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response;

class OrderCreate
{
    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $orderKey;

    /**
     * @var string|null
     */
    private $expiresOn;

    /**
     * OrderCreate constructor
     *
     * @param array $orderCreate
     */
    public function __construct(
        array $orderCreate
    ) {
        $this->url = $orderCreate['url'] ?? null;
        $this->orderKey = $orderCreate['order_key'] ?? null;
        $this->expiresOn = $orderCreate['expires_on'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getExpiresOn(): ?string
    {
        return $this->expiresOn;
    }

    /**
     * @return string|null
     */
    public function getOrderKey(): ?string
    {
        return $this->orderKey;
    }
}

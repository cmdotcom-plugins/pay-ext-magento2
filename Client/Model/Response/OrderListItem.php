<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response;

class OrderListItem
{
    /**
     * string
     */
    private $orderReference;

    /**
     * string
     */
    private $timestamp;

    /**
     * string
     */
    private $expiresOn;

    /**
     * string
     */
    private $orderKey;

    /**
     * @var string
     */
    private $url;

    /**
     * OrderDetail constructor
     *
     * @param array $orderDetail
     */
    public function __construct(
        array $orderListItem
    ) {
        $this->orderReference = $orderListItem['order_reference'];
        $this->timestamp = $orderListItem['timestamp'];
        $this->expiresOn = $orderListItem['expires_on'];
        $this->orderKey = $orderListItem['order_key'];
        $this->url = $orderListItem['url'];
    }

    /**
     * @inheritDoc
     */
    public function getOrderReference(): string
    {
        return $this->orderReference;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresOn(): string
    {
        return $this->expiresOn;
    }
    /**
     * @inheritDoc
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getOrderKey(): string
    {
        return $this->orderKey;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Data;

/**
 * Interface BrowserDetailsInterface
 *
 * @api
 */
interface BrowserDetailsInterface
{
    /**
     * Properties
     */
    public const SHOPPER_IP = 'shopper_ip';
    public const ACCEPT = 'accept';
    public const USER_AGENT = 'user_agent';

    /**
     * Get option shopperIp
     *
     * @return string|null
     */
    public function getShopperIp(): ?string;

    /**
     * Set option shopperIp
     *
     * @param string $shopperIp
     * @return $this
     */
    public function setShopperIp(string $shopperIp): BrowserDetailsInterface;

    /**
     * Get option accept
     *
     * @return string|null
     */
    public function getAccept(): ?string;

    /**
     * Set option accept
     *
     * @param string $accept
     * @return $this
     */
    public function setAccept(string $accept): BrowserDetailsInterface;

    /**
     * Get option userAgent
     *
     * @return string|null
     */
    public function getUserAgent(): ?string;

    /**
     * Set option userAgent
     *
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent(string $userAgent): BrowserDetailsInterface;
}

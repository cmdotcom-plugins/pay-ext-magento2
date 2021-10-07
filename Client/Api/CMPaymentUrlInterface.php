<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

interface CMPaymentUrlInterface
{
    /** URL type */
    public const PURPOSE_REDIRECT = 'REDIRECT';

    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @return string
     */
    public function getOrder(): string;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return string
     */
    public function getPurpose(): string;

    /**
     * @return string
     */
    public function getParameters(): string;
}

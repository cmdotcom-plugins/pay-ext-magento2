<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

interface CMPaymentInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return ?string
     */
    public function getRedirectUrl(): ?string;

    /**
     * @return \CM\Payments\Client\Api\CMPaymentUrlInterface[]|array
     */
    public function getUrls(): array;
}

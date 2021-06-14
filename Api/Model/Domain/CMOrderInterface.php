<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model\Domain;

interface CMOrderInterface
{
    public function getUrl(): string;

    public function getOrderReference(): string;

    public function getOrderKey(): string;

    public function getExpiresOn(): string;
}

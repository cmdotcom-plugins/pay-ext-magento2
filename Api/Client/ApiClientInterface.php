<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Client;

use CM\Payments\Client\Api\RequestInterface;

interface ApiClientInterface
{
    /**
     * Execute a request against the CM Payments Api.
     */
    public function execute(RequestInterface $request): array;
}

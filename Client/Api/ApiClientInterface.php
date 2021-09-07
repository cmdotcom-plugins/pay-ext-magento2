<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

interface ApiClientInterface
{
    /**
     * Execute a request against the CM Payments Api
     *
     * @throws GuzzleException
     */
    public function execute(RequestInterface $request): array;
}

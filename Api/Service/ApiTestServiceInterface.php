<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ApiTestServiceInterface
{
    /**
     * @return array
     * @throws GuzzleException|NoSuchEntityException
     */
    public function testApiConnection(): array;
}

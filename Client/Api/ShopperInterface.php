<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

use CM\Payments\Client\Model\Response\ShopperCreate;
use CM\Payments\Client\Request\ShopperCreateRequest;
use GuzzleHttp\Exception\RequestException;

interface ShopperInterface
{
    /**
     * @param ShopperCreateRequest $shopperCreateRequest
     * @return ShopperCreate
     * @throws RequestException
     */
    public function create(ShopperCreateRequest $shopperCreateRequest): ShopperCreate;
}

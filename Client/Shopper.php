<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client;

use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Api\ShopperInterface;
use CM\Payments\Client\Model\Response\ShopperCreate;
use CM\Payments\Client\Request\ShopperCreateRequest;

class Shopper implements ShopperInterface
{
    /**
     * @var ApiClientInterface
     */
    private $apiClient;

    /**
     * Shopper constructor
     *
     * @param ApiClientInterface $apiClient
     */
    public function __construct(
        ApiClientInterface $apiClient
    ) {
        $this->apiClient = $apiClient;
    }

    /**
     * @inheritDoc
     */
    public function create(ShopperCreateRequest $shopperCreateRequest): ShopperCreate
    {
        $response = $this->apiClient->execute(
            $shopperCreateRequest
        );

        return new ShopperCreate($response);
    }
}

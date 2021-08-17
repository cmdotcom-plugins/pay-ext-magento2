<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\Model\Request\ShopperCreate;

class ShopperCreateRequest implements RequestInterface
{
    /**
     * Shopper Create Endpoint
     */
    public const ENDPOINT = 'shoppers';

    /**
     * @var ShopperCreate
     */
    private $shopperCreate;

    /**
     * ShopperCreateRequest constructor
     *
     * @param ShopperCreate $shopperCreate
     */
    public function __construct(
        ShopperCreate $shopperCreate
    ) {
        $this->shopperCreate = $shopperCreate;
    }

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return self::ENDPOINT;
    }

    /**
     * @inheritDoc
     */
    public function getRequestMethod(): string
    {
        return RequestInterface::HTTP_POST;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return $this->shopperCreate->toArray();
    }
}

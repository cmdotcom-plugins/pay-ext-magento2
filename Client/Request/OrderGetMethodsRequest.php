<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;

class OrderGetMethodsRequest implements RequestInterface
{
    /**
     * Order Get Endpoint
     */
    public const ENDPOINT = 'orders';

    /**
     * @var string
     */
    private $orderKey;

    /**
     * OrderGetMethodsRequest constructor
     *
     * @param string $orderKey
     */
    public function __construct(
        string $orderKey
    ) {
        $this->orderKey = $orderKey;
    }

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return  self::ENDPOINT . '/' . $this->orderKey . '/payments/methods' ;
    }

    /**
     * @inheritDoc
     */
    public function getRequestMethod(): string
    {
        return RequestInterface::HTTP_GET;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return [];
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;

class OrderItemsCreateRequest implements RequestInterface
{
    /**
     * Order Items Create Endpoint
     */
    public const ENDPOINT = 'items';

    /**
     * @var string
     */
    private $orderKey;

    /**
     * @var OrderItemCreate[]
     */
    private $orderItems;

    /**
     * OrderItemsCreateRequest constructor
     *
     * @param string $orderKey
     * @param OrderItemCreate[] $orderItems
     */
    public function __construct(
        string $orderKey,
        array $orderItems
    ) {
        $this->orderKey = $orderKey;
        $this->orderItems = $orderItems;
    }

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return OrderCreateRequest::ENDPOINT . '/' . $this->orderKey . '/' . self::ENDPOINT;
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
        $payload = [];
        foreach ($this->orderItems as $orderItem) {
            $payload[] = $orderItem->toArray();
        }

        return $payload;
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\Model\Order;

class OrderCreateRequest implements RequestInterface
{
    /**
     * Orders Endpoint
     */
    public const ENDPOINT = 'orders';

    /**
     * @var Order
     */
    private $order;

    /**
     * OrderCreateRequest constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
        return $this->order->toArray();
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;

class OrdersRequest implements RequestInterface
{
    /**
     * Order Get Endpoint
     */
    public const ENDPOINT = 'orders';

    /**
     * @var false|string
     */
    private $date;

    /**
     * OrdersRequest constructor.
     * @param string $date
     */
    public function __construct(string $date)
    {
        $this->date = $date ?? date('Y-m-d');
    }
    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return  self::ENDPOINT;
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
        return [
           'date' => $this->date
        ];
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Api\Model\Data\OrderInterface as CMOrder;
use CM\Payments\Client\Api\RequestInterface;

class OrderGetRequest implements RequestInterface
{
    /**
     * Order Get Endpoint
     */
    public const ENDPOINT = 'orders';

    /**
     * @var CMOrder
     */
    private $cmOrder;

    /**
     * OrderGetRequest constructor.
     *
     * @param CMOrder $cmOrder
     */
    public function __construct(
        CMOrder $cmOrder
    ) {
        $this->cmOrder = $cmOrder;
    }

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return  self::ENDPOINT . '/' . $this->cmOrder->getOrderKey();
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

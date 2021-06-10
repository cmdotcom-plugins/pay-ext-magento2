<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;

class OrderGetRequest implements RequestInterface
{
    /**
     * @var array
     */
    private array $endpointParams = [];

    /**
     * Order Get Endpoint
     */
    public const ENDPOINT = 'orders/{order_key}';

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        $endpoint = self::ENDPOINT;
        if ($this->getEndpointParams()) {
            $endpoint = $this->getProcessedEndpoint($endpoint, $this->getEndpointParams());
        }
        return $endpoint;
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

    /**
     * @inheritDoc
     */
    public function getEndpointParams(): array
    {
        return $this->endpointParams;
    }

    /**
     * @inheritDoc
     */
    public function setEndpointParams(array $endpointParams): RequestInterface
    {
        $this->endpointParams = $endpointParams;

        return $this;
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return string
     */
    private function getProcessedEndpoint(string $endpoint, array $params): string
    {
        return str_replace(array_keys($params), array_values($params), $endpoint);
    }
}

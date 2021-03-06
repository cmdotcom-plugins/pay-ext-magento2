<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

interface RequestInterface
{
    /**
     * Request Methods
     */
    public const HTTP_GET = 'GET';
    public const HTTP_POST = 'POST';
    public const HTTP_PUT = 'PUT';
    public const HTTP_DELETE = 'DELETE';

    /**
     * Get endpoint
     *
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * Get the request method
     *
     * @return string
     */
    public function getRequestMethod(): string;

    /**
     * Get request body
     *
     * @return array
     */
    public function getPayload(): array;
}

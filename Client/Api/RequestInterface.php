<?php
/**
 * Copyright © 2021 cm.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

interface RequestInterface
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';

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

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\Model\Request\PaymentCreate;

class PaymentCreateRequest implements RequestInterface
{
    /**
     * Payment Create Endpoint
     */
    public const ENDPOINT = 'orders/{order_key}/payments';

    /**
     * @var PaymentCreate
     */
    private $paymentCreate;

    /**
     * @var string
     */
    private $orderKey;

    /**
     * PaymentCreateRequest constructor.
     *
     * @param string $orderKey
     * @param PaymentCreate $orderCreate
     */
    public function __construct(
        string $orderKey,
        PaymentCreate $paymentCreate
    ) {
        $this->paymentCreate = $paymentCreate;
        $this->orderKey = $orderKey;
    }

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return str_replace('{order_key}', $this->orderKey, self::ENDPOINT);
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
        return $this->paymentCreate->toArray();
    }
}

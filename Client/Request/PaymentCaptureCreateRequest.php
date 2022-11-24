<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\Model\Request\PaymentCaptureCreate;

class PaymentCaptureCreateRequest implements RequestInterface
{
    /**
     * Payment Capture Create Endpoint
     */
    public const ENDPOINT = 'orders/{order_key}/payments/{payment_id}/captures';

    /**
     * @var PaymentCaptureCreate
     */
    private $paymentCaptureCreate;

    /**
     * @var string
     */
    private $orderKey;

    /**
     * @var string
     */
    private $paymentId;

    /**
     * PaymentCreateRequest constructor
     *
     * @param string $orderKey
     * @param string $paymentId
     * @param PaymentCaptureCreate $paymentCaptureCreate
     */
    public function __construct(
        string $orderKey,
        string $paymentId,
        PaymentCaptureCreate $paymentCaptureCreate
    ) {
        $this->paymentCaptureCreate = $paymentCaptureCreate;
        $this->orderKey = $orderKey;
        $this->paymentId = $paymentId;
    }

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        $endpoint = str_replace('{order_key}', $this->orderKey, self::ENDPOINT);

        return str_replace('{payment_id}', $this->paymentId, $endpoint);
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
        return $this->paymentCaptureCreate->toArray();
    }
}

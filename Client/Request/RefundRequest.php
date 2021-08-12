<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Request;

use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\Model\Request\RefundCreate;

class RefundRequest implements RequestInterface
{
    /**
     * Refund Endpoint
     */
    public const ENDPOINT = 'orders/{order_key}/payments/{payment_id}/refunds';

    /**
     * @var RefundCreate
     */
    private $refundCreate;

    /**
     * RefundRequest constructor.
     *
     * @param RefundCreate $refundCreate
     */
    public function __construct(
        RefundCreate $refundCreate
    ) {
        $this->refundCreate = $refundCreate;
    }

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return str_replace(
            ['{order_key}', '{payment_id}'],
            [$this->refundCreate->getOrderKey(), $this->refundCreate->getPaymentId()],
            self::ENDPOINT
        );
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
        return array_filter([
            'refund_reference' => $this->refundCreate->getRefundReference(),
            'description' => $this->refundCreate->getDescription(),
            'amount' => $this->refundCreate->getAmount(),
            'currency' => $this->refundCreate->getCurrency(),
            'refund_required_date' => $this->refundCreate->getRefundRequiredDate()
        ]);
    }
}

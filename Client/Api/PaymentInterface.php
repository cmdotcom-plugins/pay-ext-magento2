<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

use CM\Payments\Client\Model\Response\PaymentCreate;
use CM\Payments\Client\Request\PaymentCaptureCreateRequest;
use CM\Payments\Client\Request\PaymentCreateRequest;
use GuzzleHttp\Exception\RequestException;

interface PaymentInterface
{
    /**
     * @param PaymentCreateRequest $paymentCreateRequest
     * @return PaymentCreate
     * @throws RequestException
     */
    public function create(PaymentCreateRequest $paymentCreateRequest): PaymentCreate;

    /**
     * @param PaymentCaptureCreateRequest $paymentCaptureCreateRequest
     *
     * @throws RequestException
     */
    public function capture(PaymentCaptureCreateRequest $paymentCaptureCreateRequest): void;
}

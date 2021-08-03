<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client;

use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Api\PaymentInterface;
use CM\Payments\Client\Model\Response\PaymentCreate;
use CM\Payments\Client\Request\PaymentCreateRequest;

class Payment implements PaymentInterface
{
    /**
     * ApiClientInterface
     */
    private $apiClient;

    /**
     * Payment constructor
     *
     * @param ApiClientInterface $apiClient
     */
    public function __construct(
        ApiClientInterface $apiClient
    ) {
        $this->apiClient = $apiClient;
    }

    /**
     * @inheritDoc
     */
    public function create(PaymentCreateRequest $paymentCreateRequest): PaymentCreate
    {
        $response = $this->apiClient->execute(
            $paymentCreateRequest
        );

        return new PaymentCreate($response);
    }
}

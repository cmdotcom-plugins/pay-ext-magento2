<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client;

use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Api\RefundInterface;
use CM\Payments\Client\Model\Request\RefundCreate;
use CM\Payments\Client\Model\Response\RefundResponse;
use CM\Payments\Client\Request\RefundRequest;

class Refund implements RefundInterface
{
    /**
     * ApiClientInterface
     */
    private $apiClient;

    /**
     * Order constructor.
     * @param ApiClientInterface $apiClient
     */
    public function __construct(ApiClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @inheritDoc
     */
    public function refund(RefundCreate $refundCreate): RefundResponse
    {
        $refundRequest = new RefundRequest($refundCreate);
        $response = $this->apiClient->execute($refundRequest);

        return new RefundResponse($response);
    }
}

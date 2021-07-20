<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client;

use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Api\OrderInterface;
use CM\Payments\Client\Model\Response\OrderCreate;
use CM\Payments\Client\Model\Response\OrderDetail;
use CM\Payments\Client\Model\Response\PaymentMethod;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Client\Request\OrderGetMethodsRequest;
use CM\Payments\Client\Request\OrderGetRequest;
use GuzzleHttp\Exception\RequestException;

class Order implements OrderInterface
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
     * @param string $orderKey
     * @return OrderDetail
     *
     * @throws RequestException
     */
    public function getDetail(string $orderKey): OrderDetail
    {
        $orderGetRequest = new OrderGetRequest($orderKey);

        $response = $this->apiClient->execute(
            $orderGetRequest
        );

        return new OrderDetail($response);
    }

    /**
     * @inheritDoc
     */
    public function getMethods(string $orderKey): array
    {
        $orderGetRequest = new OrderGetMethodsRequest($orderKey);

        $response = $this->apiClient->execute(
            $orderGetRequest
        );

        return array_map(function ($method) {
            return new PaymentMethod($method);
        }, $response);
    }

    /**
     * @inheritDoc
     */
    public function create(OrderCreateRequest $orderCreateRequest): OrderCreate
    {
        $response = $this->apiClient->execute(
            $orderCreateRequest
        );

        return new OrderCreate($response);
    }
}

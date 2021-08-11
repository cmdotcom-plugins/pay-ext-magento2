<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

use CM\Payments\Client\Model\Response\OrderCreate;
use CM\Payments\Client\Model\Response\OrderDetail;
use CM\Payments\Client\Model\Response\OrderListItem;
use CM\Payments\Client\Model\Response\PaymentMethod;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Client\Request\OrderItemsCreateRequest;
use GuzzleHttp\Exception\RequestException;

interface OrderInterface
{
    /**
     * @param string $date
     * @return OrderListItem[]
     * @throws RequestException
     */
    public function getList(string $date): array;

    /**
     * @param string $orderKey
     * @return OrderDetail
     * @throws RequestException
     */
    public function getDetail(string $orderKey): OrderDetail;

    /**
     * @param string $orderKey
     * @return PaymentMethod[]
     * @throws RequestException
     */
    public function getMethods(string $orderKey): array;

    /**
     * @param OrderCreateRequest $orderCreateRequest
     * @return OrderCreate
     * @throws RequestException
     */
    public function create(OrderCreateRequest $orderCreateRequest): OrderCreate;

    /**
     * @param OrderItemsCreateRequest $orderItemsCreateRequest
     * @return void
     * @throws RequestException
     */
    public function createItems(OrderItemsCreateRequest $orderItemsCreateRequest): array;
}

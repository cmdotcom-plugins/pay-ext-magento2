<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

use CM\Payments\Client\Model\Response\OrderCreate;
use CM\Payments\Client\Model\Response\OrderDetail;
use CM\Payments\Client\Request\OrderCreateRequest;
use GuzzleHttp\Exception\RequestException;

interface OrderInterface
{
    /**
     * @param string $orderKey
     * @return OrderDetail
     *
     * @throws RequestException
     */
    public function getDetail(string $orderKey): OrderDetail;

    /**
     * @param OrderCreateRequest $orderCreateRequest
     * @return OrderCreate
     *
     * @throws RequestException
     */
    public function create(OrderCreateRequest $orderCreateRequest): OrderCreate;
}

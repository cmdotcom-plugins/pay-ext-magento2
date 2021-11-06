<?php

namespace CM\Payments\Api\Service\Order\Item;

use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\Request\OrderItemsCreateRequest;

interface AddOrderItemsAdjustmentServiceInterface
{
    /**
     * @param int $grandTotal
     * @param string $currencyCode
     * @param OrderItemsCreateRequest|RequestInterface $orderItemsCreateRequest
     * @return OrderItemsCreateRequest
     */
    public function execute(
        int $grandTotal,
        string $currencyCode,
        OrderItemsCreateRequest $orderItemsCreateRequest
    ): OrderItemsCreateRequest;
}

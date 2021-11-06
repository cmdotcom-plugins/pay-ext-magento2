<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Item;

use CM\Payments\Api\Service\Order\Item\AddOrderItemsAdjustmentServiceInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use CM\Payments\Client\Model\Request\OrderItemCreateFactory as ClientOrderItemCreateFactory;
use CM\Payments\Client\Request\OrderItemsCreateRequest;
use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;

/**
 * Class AddOrderItemsAdjustmentService
 *
 * Magento have some rounding problems which causes errors at CM when creating Order Items.
 * Magento grandTotal and the sum of each item price are sometimes not equal.
 * In this Service we compare the grandTotal with the sum of each order item
 * and add a discount item with the difference as amount (negative or positive).
 */
class AddOrderItemsAdjustmentService implements AddOrderItemsAdjustmentServiceInterface
{
    /**
     * @var ClientOrderItemCreateFactory
     */
    private $clientOrderItemCreateFactory;

    /**
     * AddOrderItemsAdjustmentObserver constructor
     *
     * @param ClientOrderItemCreateFactory $clientOrderItemCreateFactory
     */
    public function __construct(
        ClientOrderItemCreateFactory $clientOrderItemCreateFactory
    ) {
        $this->clientOrderItemCreateFactory = $clientOrderItemCreateFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        int $grandTotal,
        string $currencyCode,
        OrderItemsCreateRequest $orderItemsCreateRequest
    ): OrderItemsCreateRequest {
        $itemsTotal = 0;
        foreach ($orderItemsCreateRequest->getPayload() as $item) {
            $itemsTotal += $item['amount'];
        }

        $max = $itemsTotal + 0.05 * 100;
        $min = $itemsTotal - 0.05 * 100;
        $difference = $grandTotal - $itemsTotal;
        if (($min <= $grandTotal) && ($grandTotal <= $max) && $difference !== 0) {
            /** @var OrderItemCreate $orderCreate */
            $orderItemCreate = $this->clientOrderItemCreateFactory->create();

            $orderItemCreate->setItemId(count($orderItemsCreateRequest->getPayload()) + 1);
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_DISCOUNT);
            $orderItemCreate->setSku(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_SKU);
            $orderItemCreate->setName(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_NAME);
            $orderItemCreate->setDescription(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_NAME);
            $orderItemCreate->setQuantity(1);
            $orderItemCreate->setUnitAmount((int)round($difference));
            $orderItemCreate->setAmount((int)round($difference));
            $orderItemCreate->setCurrency($currencyCode);
            $orderItemCreate->setVatRate(sprintf("%.1f", 0));
            $orderItemCreate->setVatAmount(0);

            $orderItemsCreateRequest->addOrderItem($orderItemCreate);
        }

        return $orderItemsCreateRequest;
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Service\Order\Item;

use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use CM\Payments\Client\Request\OrderItemsCreateRequest;
use CM\Payments\Service\Order\Item\AddOrderItemsAdjustmentService;
use CM\Payments\Test\Integration\IntegrationTestCase;

class AddOrderItemsAdjustmentServiceTest extends IntegrationTestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testAddOrderItemsAdjustmentDoNothing()
    {
        /** @var AddOrderItemsAdjustmentService $addOrderItemsAdjustmentService */
        $addOrderItemsAdjustmentService = $this->objectManager->create(AddOrderItemsAdjustmentService::class);
        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $orderItems = [new OrderItemCreate(
            '123',
            'sku',
            'test',
            'descr',
            'type',
            1,
            $magentoOrder->getOrderCurrencyCode(),
            (int)(100.00 * 100),
            (int)(100.00 * 100),
            0,
            0
        )];
        $orderItemsCreateRequestMock = $this->getMockBuilder(OrderItemsCreateRequest::class)
            ->setConstructorArgs([
                'orderKey' => '123',
                'orderItems' => $orderItems
            ])
            ->getMock();

        $orderItemsCreateRequestMock->method('getPayload')->willReturn(
            array_map(function ($item) {
                return $item->toArray();
            }, $orderItems)
        );

        $orderItemsCreateRequestMock->expects($this->never())->method('addOrderItem');
        $addOrderItemsAdjustmentService->execute(
            (int) ($magentoOrder->getGrandTotal() * 100),
            $magentoOrder->getOrderCurrencyCode(),
            $orderItemsCreateRequestMock
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testAddOrderItemsAdjustmentSubtract()
    {
        /** @var AddOrderItemsAdjustmentService $addOrderItemsAdjustmentService */
        $addOrderItemsAdjustmentService = $this->objectManager->create(AddOrderItemsAdjustmentService::class);
        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $orderItems = [new OrderItemCreate(
            '123',
            'sku',
            'test',
            'descr',
            'type',
            1,
            $magentoOrder->getOrderCurrencyCode(),
            (int)(100.03 * 100),
            (int)(100.03 * 100),
            0,
            0
        )];
        $orderItemsCreateRequestMock = $this->getMockBuilder(OrderItemsCreateRequest::class)
            ->setConstructorArgs([
                'orderKey' => '123',
                'orderItems' => $orderItems
            ])
            ->getMock();

        $orderItemsCreateRequestMock->method('getPayload')->willReturn(
            array_map(function ($item) {
                return $item->toArray();
            }, $orderItems)
        );

        $orderItemCreate = $this->getOrderItemCreate(-3, -3, $magentoOrder->getOrderCurrencyCode());

        $orderItemsCreateRequestMock->expects($this->once())->method('addOrderItem')->with($orderItemCreate);
        $addOrderItemsAdjustmentService->execute(
            (int) ($magentoOrder->getGrandTotal() * 100),
            $magentoOrder->getOrderCurrencyCode(),
            $orderItemsCreateRequestMock
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testAddOrderItemsAdjustmentAddition()
    {
        /** @var AddOrderItemsAdjustmentService $addOrderItemsAdjustmentService */
        $addOrderItemsAdjustmentService = $this->objectManager->create(AddOrderItemsAdjustmentService::class);
        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $orderItems = [new OrderItemCreate(
            '123',
            'sku',
            'test',
            'descr',
            'type',
            1,
            $magentoOrder->getOrderCurrencyCode(),
            (int)(99.99 * 100),
            (int)(99.99 * 100),
            0,
            0
        )];
        $orderItemsCreateRequestMock = $this->createMock(OrderItemsCreateRequest::class);

        $orderItemsCreateRequestMock->method('getPayload')->willReturn(
            array_map(function ($item) {
                return $item->toArray();
            }, $orderItems)
        );

        $orderItemCreate = $this->getOrderItemCreate(1, 1, $magentoOrder->getOrderCurrencyCode());

        $orderItemsCreateRequestMock->expects($this->once())->method('addOrderItem')->with($orderItemCreate);
        $addOrderItemsAdjustmentService->execute(
            (int) ($magentoOrder->getGrandTotal() * 100),
            $magentoOrder->getOrderCurrencyCode(),
            $orderItemsCreateRequestMock
        );
    }

    /**
     * @param int $unitAmount
     * @param int $amount
     * @return OrderItemCreate
     */
    private function getOrderItemCreate(int $unitAmount, int $amount, string $currencyCode): OrderItemCreate
    {
        $orderItemCreate = $this->objectManager->create(OrderItemCreate::class);
        $orderItemCreate->setItemId(2);
        $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_DISCOUNT);
        $orderItemCreate->setSku(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_SKU);
        $orderItemCreate->setName(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_NAME);
        $orderItemCreate->setDescription(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_NAME);
        $orderItemCreate->setQuantity(1);
        $orderItemCreate->setUnitAmount($unitAmount);
        $orderItemCreate->setAmount($amount);
        $orderItemCreate->setCurrency($currencyCode);
        $orderItemCreate->setVatRate(sprintf("%.1f", 0));
        $orderItemCreate->setVatAmount(0);

        return $orderItemCreate;
    }
}

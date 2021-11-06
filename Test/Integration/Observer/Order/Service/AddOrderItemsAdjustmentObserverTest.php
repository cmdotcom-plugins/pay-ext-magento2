<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Observer\Order\Service;

use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use CM\Payments\Client\Request\OrderItemsCreateRequest;
use CM\Payments\Observer\AdditionalDataAssignObserver;
use CM\Payments\Observer\Order\Service\AddOrderItemsAdjustmentObserver;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;

class AddOrderItemsAdjustmentObserverTest extends IntegrationTestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testAddOrderItemsAdjustmentAddition()
    {
        /** @var AdditionalDataAssignObserver $additionalDataAssignObserver */
        $addOrderItemsAdjustmentObserver = $this->objectManager->create(AddOrderItemsAdjustmentObserver::class);

        $data =  $this->objectManager->create(DataObject::class);
        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $data->setData('order', $magentoOrder);

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

        $data->setData('orderItemsCreateRequest', $orderItemsCreateRequestMock);
        $event = $this->objectManager->create(Event::class, ['data' => [
            'order' => $magentoOrder,
            'orderItemsCreateRequest' => $orderItemsCreateRequestMock
        ]]);

        /** @var Observer $observer */
        $observer = $this->objectManager->create(Observer::class, ['event' => $event]);
        $observer->setEvent($event);

        $orderItemsCreateRequestMock->expects($this->once())->method('addOrderItem');
        $addOrderItemsAdjustmentObserver->execute($observer);
    }
}

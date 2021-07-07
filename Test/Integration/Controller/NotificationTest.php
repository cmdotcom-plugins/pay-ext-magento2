<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Controller\Checkout;

use CM\Payments\Service\OrderTransactionService;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\TestCase\AbstractController;

class NotificationTest extends AbstractController
{
    public function testDoNothingWhenOrderKeyNotProvided()
    {
        $this->dispatch('cmpayments/payment/notification');

        $this->assertEquals(404, $this->getResponse()->getStatusCode());
    }

    public function testNotificationException()
    {
        $this->getRequest()->setParam('id', 'test123');

        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock
            ->expects($this->once())
            ->method('process')
            ->willThrowException(new NoSuchEntityException());

        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->dispatch('cmpayments/payment/notification');

        $this->assertEquals(400, $this->getResponse()->getStatusCode());
        $this->assertEquals(json_encode(['message' => __('No such entity')]), $this->getResponse()->getBody());
    }

    public function testNotificationUpdate()
    {
        $this->getRequest()->setParam('id', 'test123');

        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->once())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->dispatch('cmpayments/payment/notification');
        $this->assertEquals(200, $this->getResponse()->getStatusCode());
    }
}

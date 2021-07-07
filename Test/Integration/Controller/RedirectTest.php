<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Controller\Checkout;

use CM\Payments\Model\Domain\CMOrder;
use CM\Payments\Service\OrderService;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Message\MessageInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractController;

class RedirectTest extends AbstractController
{
    public function testDoesRedirectsToCartWhenNoOrderIsFound()
    {
        $this->dispatch('cmpayments/menu/redirect');

        $this->assertRedirect($this->stringContains('checkout/cart'));
    }

    /**
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectsToCartOnException()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $orderService = $this->createMock(OrderService::class);
        $orderService
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('[TEST] Something went wrong'));
        $this->_objectManager->addSharedInstance($orderService, OrderService::class);

        $this->dispatch('cmpayments/menu/redirect');

        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertSessionMessages(
            $this->equalTo(['Something went wrong while creating the order.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirect()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $order = $this->loadOrderById('100000001');
        $cmOrder = new CMOrder(
            'https://test.nl',
            '100000001',
            'test123',
            strtotime('+1 hour')
        );

        $orderService = $this->createMock(OrderService::class);
        $orderService->expects($this->once())->method('create')->with($order->getId())->willReturn($cmOrder);
        $this->_objectManager->addSharedInstance($orderService, OrderService::class);

        $this->dispatch('cmpayments/menu/redirect');

        $this->assertRedirect($this->stringContains('test.nl'));
    }

    /**
     * @param $orderId
     * @return OrderInterface
     */
    private function loadOrderById($orderId)
    {
        $repository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $builder = $this->_objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $builder->addFilter('increment_id', $orderId, 'eq')->create();

        $orderList = $repository->getList($searchCriteria)->getItems();

        return array_shift($orderList);
    }
}

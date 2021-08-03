<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Controller\Checkout;

use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Message\MessageInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractController;

class ResultTest extends AbstractController
{
    public function testRedirectToCheckoutWhenOrderReferenceNotProvided()
    {
        $this->getRequest()->setParam('status', 'SUCCESS');
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));

        $this->assertSessionMessages(
            $this->equalTo(['The order reference is not valid!']),
            MessageInterface::TYPE_ERROR
        );
    }

    public function testRedirectToCheckoutWhenStatusNotProvided()
    {
        $this->getRequest()->setParam('order_reference', '100000001');
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));

        $this->assertSessionMessages(
            $this->equalTo(['The order reference is not valid!']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToCheckoutWhenOrderIdsNotEqual()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $this->getRequest()->setParam('order_reference', '100000002');
        $this->getRequest()->setParam('status', 'SUCCESS');
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertSessionMessages(
            $this->equalTo(['The order reference is not valid!']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToCheckoutWhenStatusIsCancelled()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->getRequest()->setParam('status', OrderCreate::STATUS_CANCELLED);
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertSessionMessages(
            $this->equalTo(['The order was cancelled because of payment errors!']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToCheckoutWhenStatusIsError()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->getRequest()->setParam('status', OrderCreate::STATUS_ERROR);
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertSessionMessages(
            $this->equalTo(['The order was cancelled because of payment errors!']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToCheckoutWhenSomethingWentWrong()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getLastRealOrder')->willThrowException(new \Exception('[TEST] Something went wrong'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->getRequest()->setParam('status', OrderCreate::STATUS_SUCCESS);
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertSessionMessages(
            $this->equalTo(['Something went wrong with processing the order.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToSuccessPage()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->getRequest()->setParam('status', OrderCreate::STATUS_SUCCESS);
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/onepage/success'));
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

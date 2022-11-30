<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Controller;

use CM\Payments\Client\Model\Request\OrderCreate;
use CM\Payments\Service\OrderTransactionService;
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
        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->never())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->getRequest()->setParam('status', 'SUCCESS');
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));
    }

    public function testRedirectToCheckoutWhenStatusNotProvided()
    {
        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->never())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/custom_error_url https://test.nl
     */
    public function testRedirectToCheckoutWithCustomErrorUrl()
    {
        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->never())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('https://test.nl/?utm_nooverride=1'));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToCheckoutWhenStatusIsCancelled()
    {
        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->never())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->getRequest()->setParam('status', OrderCreate::STATUS_CANCELLED);
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertSessionMessages(
            $this->equalTo(['Something went wrong while processing the order.']),
            MessageInterface::TYPE_WARNING
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToCheckoutWhenStatusIsError()
    {
        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->never())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->getRequest()->setParam('status', OrderCreate::STATUS_ERROR);
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertSessionMessages(
            $this->equalTo(['Something went wrong while processing the order.']),
            MessageInterface::TYPE_WARNING
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToSuccessPage()
    {
        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->once())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->getRequest()->setParam('status', OrderCreate::STATUS_SUCCESS);
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('checkout/onepage/success'));
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/custom_success_url https://test.nl/success
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToCustomSuccessPage()
    {
        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->once())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

        $this->getRequest()->setParam('order_reference', '100000001');
        $this->getRequest()->setParam('status', OrderCreate::STATUS_SUCCESS);
        $this->dispatch('cmpayments/payment/result');

        $this->assertRedirect($this->stringContains('https://test.nl/success?utm_nooverride=1&order_increment_id=100000001'));
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/update_on_result_page 0
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectToSuccessPageWithoutUpdateOrder()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $orderTransactionServiceMock = $this->createMock(OrderTransactionService::class);
        $orderTransactionServiceMock->expects($this->never())->method('process');
        $this->_objectManager->addSharedInstance($orderTransactionServiceMock, OrderTransactionService::class);

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

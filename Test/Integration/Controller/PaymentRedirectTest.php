<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Controller\Checkout;

use CM\Payments\Client\Model\CMPayment;
use CM\Payments\Client\Model\CMPaymentUrl;
use CM\Payments\Model\Domain\CMOrder;
use CM\Payments\Service\OrderService;
use CM\Payments\Service\PaymentService;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractController;

class PaymentRedirectTest extends AbstractController
{
    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testPaypalRedirect()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $order = $this->loadOrderById('100000001');
        $cmOrder = new CMOrder(
            'https://test-cm.nl',
            '100000001',
            '0287A1617D93780EF28044B98438BF2F',
            strtotime('+1 hour')
        );

        $cmPayment = new CMPayment(
            'pid4911261016t',
            'REDIRECTED_FOR_AUTHENTICATION',
            //phpcs:ignore
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=EC-0HD94326F3768884E',
            [
                new CMPaymentUrl(
                    'REDIRECT',
                    'GET',
                    //phpcs:ignore
                    'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=EC-0HD94326F3768884E',
                    '1'
                )
            ]
        );

        $orderService = $this->createMock(OrderService::class);
        $orderService->expects($this->once())->method('create')->with($order->getId())->willReturn($cmOrder);
        $this->_objectManager->addSharedInstance($orderService, OrderService::class);

        $paymentService = $this->createMock(PaymentService::class);
        $paymentService->expects($this->once())->method('create')->with($order->getId())->willReturn($cmPayment);
        $this->_objectManager->addSharedInstance($paymentService, PaymentService::class);

        $this->dispatch('cmpayments/payment/redirect');

        $this->assertRedirect($this->stringContains('paypal.com'));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testEmptyOrderReference()
    {
        $sessionMock = $this->createMock(Session::class);

        $sessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $order = $this->loadOrderById('100000001');
        $cmOrder = new CMOrder(
            'https://test-cm.nl',
            '',
            '0287A1617D93780EF28044B98438BF2F',
            strtotime('+1 hour')
        );

        $orderService = $this->createMock(OrderService::class);
        $orderService->expects($this->once())->method('create')->with($order->getId())->willReturn($cmOrder);
        $this->_objectManager->addSharedInstance($orderService, OrderService::class);

        $this->dispatch('cmpayments/payment/redirect');

        $this->assertRedirect($this->stringContains('checkout/cart'));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testEmptyOrderRedirectUrl()
    {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())->method('getLastRealOrder')->willReturn($this->loadOrderById('100000001'));
        $this->_objectManager->addSharedInstance($sessionMock, Session::class);

        $order = $this->loadOrderById('100000001');
        $cmOrder = new CMOrder(
            'https://test-cm.nl',
            '100000001',
            '0287A1617D93780EF28044B98438BF2F',
            strtotime('+1 hour')
        );

        $cmPayment = new CMPayment(
            'pid4911261016t',
            'REDIRECTED_FOR_AUTHENTICATION',
            //phpcs:ignore
            '',
            [
                new CMPaymentUrl(
                    'REDIRECT',
                    'GET',
                    //phpcs:ignore
                    '',
                    '1'
                )
            ]
        );

        $orderService = $this->createMock(OrderService::class);
        $orderService->expects($this->once())->method('create')->with($order->getId())->willReturn($cmOrder);
        $this->_objectManager->addSharedInstance($orderService, OrderService::class);

        $paymentService = $this->createMock(PaymentService::class);
        $paymentService->expects($this->once())->method('create')->with($order->getId())->willReturn($cmPayment);
        $this->_objectManager->addSharedInstance($paymentService, PaymentService::class);

        $this->dispatch('cmpayments/payment/redirect');

        $this->assertRedirect($this->stringContains('checkout/cart'));
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

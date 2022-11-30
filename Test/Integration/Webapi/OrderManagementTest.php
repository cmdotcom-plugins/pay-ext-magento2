<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Webapi;

use CM\Payments\Api\Model\Data\OrderInterfaceFactory;
use CM\Payments\Api\Model\Domain\CMOrderInterfaceFactory;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\ApiClient;
use CM\Payments\Exception\EmptyOrderKeyException;
use CM\Payments\Exception\PaymentMethodNotFoundException;
use CM\Payments\Test\Integration\IntegrationTestCase;
use CM\Payments\Webapi\OrderManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderManagementTest extends IntegrationTestCase
{
    /**
     * @var ApiClientInterface|MockObject
     */
    private $clientMock;

    /** @var OrderManagement|mixed */
    private $orderManagement;

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     */
    public function testProcessOrderNotFound()
    {
        $this->clientMock->expects($this->never())->method('execute');

        $this->expectException(NoSuchEntityException::class);
        $this->orderManagement->processOrder(1);
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testProcessPaymentNotFound()
    {
        $this->clientMock->expects($this->never())->method('execute');
        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $this->setPaymentMethod($magentoOrder, 'checkmo');

        $this->expectException(PaymentMethodNotFoundException::class);
        $this->orderManagement->processOrder((int)$magentoOrder->getEntityId());
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testProcessOrderEmptyCMOrder()
    {
        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturn(
            // CM Shopper create
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ],
            // CM Order create
            [
                'order_key' => '',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $this->setPaymentMethod($magentoOrder, 'cm_payments');

        $this->expectException(EmptyOrderKeyException::class);
        $this->expectExceptionMessage('Empty order key');

        $this->orderManagement->processOrder((int)$magentoOrder->getEntityId());
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/mode direct
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     */
    public function testProcessOrderPaymentWithoutRedirect()
    {
        $this->clientMock->expects($this->exactly(3))->method('execute')->willReturn(
            // CM Shopper create
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ],
            // CM Order create
            [
                'order_key' => '0287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            // CM Payment create
            [
                'id' => 'p123',
                'status' => 'REDIRECTED_FOR_AUTHORIZATION',
                'urls' => []
            ]
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $this->setPaymentMethod($magentoOrder, 'cm_payments_paypal');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('No redirect url found in payment response.');

        $this->orderManagement->processOrder((int)$magentoOrder->getEntityId());
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/mode direct
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testProcessOrder()
    {
        $this->clientMock->expects($this->exactly(3))->method('execute')->willReturnOnConsecutiveCalls(
            // CM Shopper create
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ],
            // CM Order create
            [
                'order_key' => '0287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            // CM Payment create
            [
                'id' => 'p123',
                'status' => 'REDIRECTED_FOR_AUTHORIZATION',
                'urls' => [
                    0 => [
                        'purpose' => 'REDIRECT',
                        'method' => 'GET',
                        //phpcs:ignore
                        'url' => 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=EC-0HD94326F3768884E',
                        'order' => 1,
                    ],
                ]
            ]
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $this->setPaymentMethod($magentoOrder, 'cm_payments_paypal');

        $response = $this->orderManagement->processOrder((int) $magentoOrder->getEntityId());

        $this->assertEquals('p123', $response->getId());
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testProcessOrderWithoutPayment()
    {
        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
        // CM Shopper create
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ],
            // CM Order create
            [
                'order_key' => '0287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ]
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);
        $this->setPaymentMethod($magentoOrder, 'cm_payments');

        $response = $this->orderManagement->processOrder((int) $magentoOrder->getEntityId());

        $this->assertEquals('0287A1617D93780EF28044B98438BF2F', $response->getId());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ApiClientInterface::class);
        $this->objectManager->addSharedInstance($this->clientMock, ApiClient::class);
        $this->orderManagement = $this->objectManager->create(OrderManagement::class);
    }

    /**
     * @param OrderInterface $magentoOrder
     * @param string $method
     */
    private function setPaymentMethod(OrderInterface $magentoOrder, string $method): void
    {
        $payment = $magentoOrder->getPayment();
        $payment->setMethod($method);
        $magentoOrder->setPayment($payment);
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);
    }
}

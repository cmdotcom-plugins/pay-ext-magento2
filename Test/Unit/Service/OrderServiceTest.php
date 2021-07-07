<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */


declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Model\Data\OrderInterface;
use CM\Payments\Api\Model\Data\OrderInterfaceFactory as CMOrderFactory;
use CM\Payments\Api\Model\Domain\CMOrderInterface;
use CM\Payments\Api\Model\Domain\CMOrderInterfaceFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Order as ClientApiOrder;
use CM\Payments\Client\Model\OrderCreate;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\Data\Order;
use CM\Payments\Model\Domain\CMOrder;
use CM\Payments\Service\OrderService;
use CM\Payments\Test\Unit\UnitTestCase;
use Exception;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\OrderRepository;

class OrderServiceTest extends UnitTestCase
{
    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var OrderRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ClientApiOrder
     */
    private $orderClientMock;

    /**
     * @var CMOrderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderInterfaceFactoryMock;

    /**
     * @var CMOrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmOrderRepositoryMock;

    /**
     * @var OrderRequestBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRequestBuilderMock;

    /**
     * @var CMOrderInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmOrderInterfaceFactoryMock;

    /**
     * @var CMPaymentsLogger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentsLoggerMock;

    public function testCreateOrder()
    {
        $this->orderClientMock->method('create')->willReturn(
            new \CM\Payments\Client\Model\Response\OrderCreate(
                [
                    'order_key' => '0287A1617D93780EF28044B98438BF2F',
                    //phpcs:ignore
                    'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                    'expires_on' => '2021-07-12T08:10:57Z'
                ]
            )
        );

        $this->assertSame(
        //phpcs:ignore
            'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
            $this->orderService->create('1')->getUrl()
        );
    }

    public function testCreateOrderShouldThrowErrorWhenOrderKeyIsEmpty()
    {
        $this->orderClientMock->method('create')->willReturn(
            new \CM\Payments\Client\Model\Response\OrderCreate(
                [
                    'order_key' => '',
                    //phpcs:ignore
                    'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                    'expires_on' => '2021-07-12T08:10:57Z'
                ]
            )
        );

        $this->expectException(Exception::class);

        $this->orderService->create('1');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmOrderRepositoryMock = $this->getMockBuilder(CMOrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderClientMock = $this->getMockBuilder(ClientApiOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRequestBuilderMock = $this->getMockBuilder(OrderRequestBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderInterfaceFactoryMock = $this->getMockupFactory(
            Order::class,
            OrderInterface::class
        );

        $this->cmOrderInterfaceFactoryMock = $this->getMockupFactory(
            CMOrder::class,
            CMOrderInterface::class
        );

        $this->cmPaymentsLoggerMock = $this->getMockBuilder(CMPaymentsLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryMock->method('get')->willReturn($this->getOrderMock());
        $this->orderRepositoryMock->method('save');
        $this->cmOrderRepositoryMock->method('save');

        $returnUrls = [
            'success' => '',
            'pending' => '',
            'cancelled' => '',
            'error' => ''
        ];
        $this->orderRequestBuilderMock->method('create')->willReturn(
            new OrderCreateRequest(
                new OrderCreate(
                    '000000001',
                    2000,
                    'EUR',
                    self::ADDRESS_DATA['email_address'],
                    self::LANGUAGE,
                    self::ADDRESS_DATA['country_code'],
                    self::PAYMENT_PROFILE,
                    $returnUrls
                )
            )
        );

        $this->orderService = new OrderService(
            $this->orderRepositoryMock,
            $this->orderClientMock,
            $this->orderInterfaceFactoryMock,
            $this->cmOrderRepositoryMock,
            $this->orderRequestBuilderMock,
            $this->cmOrderInterfaceFactoryMock,
            $this->cmPaymentsLoggerMock
        );
    }

    /**
     * @return OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderMock()
    {
        $shippingAddressMock = $this->createConfiguredMock(
            OrderAddressInterface::class,
            [
                'getEmail' => static::ADDRESS_DATA['email_address'],
                'getCountryId' => static::ADDRESS_DATA['country_code']
            ]
        );

        $paymentMock = $this->getMockBuilder(OrderPaymentInterface::class)
            ->setMethods(['getAdditionalInformation', 'setAdditionalInformation'])
            ->getMockForAbstractClass();
        $paymentMock->method('getAdditionalInformation')->willReturn([]);
        $paymentMock->method('setAdditionalInformation')->willReturnSelf();

        $orderMock = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->method('getEntityId')->willReturn('1');
        $orderMock->method('getIncrementId')->willReturn('000000001');
        $orderMock->method('getOrderCurrencyCode')->willReturn('EUR');
        $orderMock->method('getStoreId')->willReturn(1);
        $orderMock->method('getBillingAddress')->willReturn($shippingAddressMock);
        $orderMock->method('getGrandTotal')->willReturn(50.99);
        $orderMock->method('getPayment')->willReturn($paymentMock);

        return $orderMock;
    }
}

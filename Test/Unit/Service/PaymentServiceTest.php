<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Model\Data\OrderInterface;
use CM\Payments\Api\Model\Data\PaymentInterface as CMPaymentDataInterface;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\PaymentRequestBuilderInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Model\CMPayment;
use CM\Payments\Client\Model\CMPaymentFactory;
use CM\Payments\Client\Model\Request\PaymentCreate;
use CM\Payments\Client\Payment as ClientApiPayment;
use CM\Payments\Client\Request\PaymentCreateRequest;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\ConfigProvider;
use CM\Payments\Model\Data\Payment as CMPaymentData;
use CM\Payments\Service\PaymentService;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\OrderRepository;

class PaymentServiceTest extends UnitTestCase
{
    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    /**
     * @var OrderRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ClientApiPayment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentClientMock;

    /**
     * @var PaymentRequestBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentRequestBuilderMock;

    /**
     * @var CMPaymentDataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentDataFactoryMock;

    /**
     * @var CMPaymentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentFactoryMock;

    /**
     * @var  CMPaymentRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentRepositoryMock;

    /**
     * @var  CMOrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmOrderRepositoryMock;

    /**
     * @var CMPaymentsLogger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentsLoggerMock;

    /**
     * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    public function testCreateIdealPayment()
    {
        $this->paymentClientMock->expects($this->once())->method('create')->willReturn(
            new \CM\Payments\Client\Model\Response\PaymentCreate(
                [
                    'id' => 'pid4911257676t',
                    'status' => 'REDIRECTED_FOR_AUTHORIZATION',
                    'urls' => [
                        0 => [
                            'purpose' => 'REDIRECT',
                            'method' => 'GET',
                            //phpcs:ignore
                            'url' => 'https://test.docdatapayments.com/ps_sim/idealbanksimulator.jsf?trxid=1625579689224&ec=4911257676&returnUrl=https%3A%2F%2Ftestsecure.docdatapayments.com%2Fps%2FreturnFromAuthorization%3FpaymentReference%3D49112576765AD00EC846B52EAED61E9FC2530CFF90%26checkDigitId%3D49112576765AD00EC846B52EAED61E9FC2530CFF90',
                            'order' => 1,
                        ],
                    ]
                ]
            )
        );

        $order = $this->getOrderMock();
        $payment = $this->paymentService->create((string)$order->getEntityId());

        $this->assertNotNull(
            $payment->getId()
        );
    }

    public function testEventDispatch()
    {
        $paymentCreateResponse =  new \CM\Payments\Client\Model\Response\PaymentCreate(
            [
                'id' => 'pid4911257676t',
                'status' => 'REDIRECTED_FOR_AUTHORIZATION',
                'urls' => [
                    0 => [
                        'purpose' => 'REDIRECT',
                        'method' => 'GET',
                        //phpcs:ignore
                        'url' => 'https://test.docdatapayments.com/ps_sim/idealbanksimulator.jsf?trxid=1625579689224&ec=4911257676&returnUrl=https%3A%2F%2Ftestsecure.docdatapayments.com%2Fps%2FreturnFromAuthorization%3FpaymentReference%3D49112576765AD00EC846B52EAED61E9FC2530CFF90%26checkDigitId%3D49112576765AD00EC846B52EAED61E9FC2530CFF90',
                        'order' => 1,
                    ],
                ]
            ]
        );

        $this->paymentClientMock->expects($this->once())->method('create')->willReturn(
            $paymentCreateResponse
        );
        $order = $this->getOrderMock();
        $paymentCreate = new PaymentCreate(
            MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_IDEAL],
            [
                'ideal_details' => ['issuer_id' => 'INGBNL2A']
            ]
        );
        $paymentCreateRequest = new PaymentCreateRequest('0287A1617D93780EF28044B98438BF2F', $paymentCreate);

        $cmPayment = new CMPayment(
            $paymentCreateResponse->getId(),
            $paymentCreateResponse->getStatus(),
            $paymentCreateResponse->getRedirectUrl(),
            $paymentCreateResponse->getUrls()
        );

        $this->eventManagerMock->expects($this->exactly(2))->method('dispatch')->withConsecutive(
            ['cmpayments_before_payment_create', ['order' => $order, 'paymentCreateRequest' => $paymentCreateRequest]],
            ['cmpayments_after_payment_create', ['order' => $order, 'cmPayment' => $cmPayment]]
        );

        $this->paymentService->create((string)$order->getEntityId());
    }

    /**
     * @return OrderInterface
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
        $orderMock->method('getShippingAddress')->willReturn($shippingAddressMock);
        $orderMock->method('getGrandTotal')->willReturn(99.99);
        $orderMock->method('getPayment')->willReturn($paymentMock);

        return $orderMock;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentClientMock = $this->getMockBuilder(ClientApiPayment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentRequestBuilderMock = $this->getMockBuilder(PaymentRequestBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmPaymentDataFactoryMock = $this->getMockupFactory(
            CMPaymentData::class,
            CMPaymentDataInterface::class
        );

        $this->cmPaymentFactoryMock = $this->getMockupFactory(
            CMPayment::class
        );

        $this->cmPaymentRepositoryMock = $this->getMockBuilder(CMPaymentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmPaymentRepositoryMock = $this->getMockBuilder(CMPaymentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmOrderRepositoryMock = $this->getMockBuilder(CMOrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmPaymentsLoggerMock = $this->getMockBuilder(CMPaymentsLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryMock->method('get')->willReturn($this->getOrderMock());
        $this->orderRepositoryMock->method('save');
        $this->cmOrderRepositoryMock->method('save');

        $this->paymentRequestBuilderMock->method('create')->willReturn(
            new PaymentCreateRequest(
                '0287A1617D93780EF28044B98438BF2F',
                new PaymentCreate(
                    MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_IDEAL],
                    [
                        'ideal_details' => ['issuer_id' => 'INGBNL2A']
                    ]
                )
            )
        );

        $this->paymentService = new PaymentService(
            $this->orderRepositoryMock,
            $this->paymentClientMock,
            $this->paymentRequestBuilderMock,
            $this->cmPaymentDataFactoryMock,
            $this->cmPaymentFactoryMock,
            $this->cmPaymentRepositoryMock,
            $this->cmOrderRepositoryMock,
            $this->eventManagerMock,
            $this->cmPaymentsLoggerMock
        );
    }
}

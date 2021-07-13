<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Client\Model\Request\PaymentCreate;
use CM\Payments\Client\Request\PaymentCreateRequest;
use CM\Payments\Service\Payment\Request\Part\IdealDetails;
use CM\Payments\Service\Payment\Request\Part\Method;
use CM\Payments\Service\PaymentRequestBuilder;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Model\ConfigProvider;

class PaymentRequestBuilderTest extends UnitTestCase
{
    /**
     * @var \CM\Payments\Service\PaymentRequestBuilder
     */
    private $paymentRequestBuilder;

    /**
     * @var \CM\Payments\Api\Config\ConfigInterface
     */
    private $configMock;

    public function testCreateIdealPaymentRequestBuilder()
    {
        $orderMock = $this->getOrderMock(ConfigProvider::CODE_IDEAL);
        $orderKey = '0287A1617D93780EF28044B98438BF2F';
        $paymentRequest = $this->paymentRequestBuilder->create($orderMock, $orderKey);

        $this->assertSame(
            MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_IDEAL],
            $paymentRequest->getPayload()['method']
        );

        $this->assertSame(
            'INGBNL2A',
            $paymentRequest->getPayload()['ideal_details']['issuer_id']
        );
    }

    public function testCreatePaypalPaymentRequestBuilder()
    {
        $orderMock = $this->getOrderMock(ConfigProvider::CODE_PAYPAL);
        $orderKey = '0287A1617D93780EF28044B98438BF2F';
        $paymentRequest = $this->paymentRequestBuilder->create($orderMock, $orderKey);

        $this->assertSame(
            MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_PAYPAL],
            $paymentRequest->getPayload()['method']
        );
    }

    public function testCreateBanContactPaymentRequestBuilder()
    {
        $orderMock = $this->getOrderMock(ConfigProvider::CODE_BANCONTACT);
        $orderKey = '0287A1617D93780EF28044B98438BF2F';
        $paymentRequest = $this->paymentRequestBuilder->create($orderMock, $orderKey);

        $this->assertSame(
            MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_BANCONTACT],
            $paymentRequest->getPayload()['method']
        );
    }

    public function testCreateCreditCardPaymentRequestBuilder()
    {
        $orderMock = $this->getOrderMock(ConfigProvider::CODE_CREDIT_CARD);
        $orderKey = '0287A1617D93780EF28044B98438BF2F';
        $paymentRequest = $this->paymentRequestBuilder->create($orderMock, $orderKey);

        $this->assertSame(
            MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_CREDIT_CARD],
            $paymentRequest->getPayload()['method']
        );
    }

    /**
     * @param string $paymentMethod
     * @return OrderInterface
     */
    private function getOrderMock(string $paymentMethod): OrderInterface
    {
        $shippingAddressMock = $this->createConfiguredMock(
            OrderAddressInterface::class,
            [
                'getEmail' => static::ADDRESS_DATA['email_address'],
                'getCountryId' => static::ADDRESS_DATA['country_code']
            ]
        );

        $orderMock = $this->createMock(Order::class);
        $paymentMock = $this->createMock(OrderPaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn($paymentMethod);

        if ($paymentMethod == ConfigProvider::CODE_IDEAL) {
            $paymentMock->method('getAdditionalInformation')->willReturn(
                [
                    'selected_issuer' => 'INGBNL2A'
                ]
            );
        }

        $paymentMock->method('setAdditionalInformation')->willReturnSelf();

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

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $clientPaymentCreateFactoryMock = $this->getMockupFactory(PaymentCreate::class);
        $paymentCreateRequestFactoryMock = $this->getMockupFactory(PaymentCreateRequest::class);

        $this->paymentRequestBuilder = new PaymentRequestBuilder(
            $this->configMock,
            $clientPaymentCreateFactoryMock,
            $paymentCreateRequestFactoryMock,
            [
                new IdealDetails(),
                new Method()
            ]
        );
    }
}

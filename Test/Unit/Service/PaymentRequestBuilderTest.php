<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

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

    public function testCreateIdealPaymentRequestBuilder()
    {
        $orderMock = $this->getOrderMock(ConfigProvider::CODE_IDEAL);
        $orderKey = '0287A1617D93780EF28044B98438BF2F';
        $paymentRequest = $this->paymentRequestBuilder->create('1', $orderKey, $orderMock);

        $this->assertSame(
            MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_IDEAL],
            $paymentRequest->getPayload()['method']
        );
    }

    public function testCreatePaypalPaymentRequestBuilder()
    {
        $orderMock = $this->getOrderMock(ConfigProvider::CODE_PAYPAL);
        $orderKey = '0287A1617D93780EF28044B98438BF2F';
        $paymentRequest = $this->paymentRequestBuilder->create('1', $orderKey, $orderMock);

        $this->assertSame(
            MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_PAYPAL],
            $paymentRequest->getPayload()['method']
        );
    }

    public function testCreateElvPaymentRequestBuilder()
    {
        $orderMock = $this->getOrderMock(ConfigProvider::CODE_ELV);
        $orderKey = '0287A1617D93780EF28044B98438BF2F';
        $paymentRequest = $this->paymentRequestBuilder->create('1', $orderKey, $orderMock);

        $this->assertSame(
            MethodServiceInterface::API_METHODS_MAPPING[ConfigProvider::CODE_ELV],
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

        $clientPaymentCreateFactoryMock = $this->getMockupFactory(PaymentCreate::class);
        $paymentCreateRequestFactoryMock = $this->getMockupFactory(PaymentCreateRequest::class);

        $this->paymentRequestBuilder = new PaymentRequestBuilder(
            $clientPaymentCreateFactoryMock,
            $paymentCreateRequestFactoryMock,
            [
                new IdealDetails(),
                new Method()
            ]
        );
    }
}

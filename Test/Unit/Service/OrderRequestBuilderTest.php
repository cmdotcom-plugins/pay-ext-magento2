<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use CM\Payments\Client\Model\Response\ShopperCreate;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Service\Order\Request\Part\Amount;
use CM\Payments\Service\Order\Request\Part\BillingAddressKey;
use CM\Payments\Service\Order\Request\Part\Country;
use CM\Payments\Service\Order\Request\Part\Currency;
use CM\Payments\Service\Order\Request\Part\Email;
use CM\Payments\Service\Order\Request\Part\Expiry;
use CM\Payments\Service\Order\Request\Part\Language;
use CM\Payments\Service\Order\Request\Part\OrderId;
use CM\Payments\Service\Order\Request\Part\PaymentProfile;
use CM\Payments\Service\Order\Request\Part\ReturnUrls;
use CM\Payments\Service\OrderRequestBuilder;
use CM\Payments\Service\Quote\Request\Part\OrderId as QuoteOrderId;
use CM\Payments\Service\ShopperService;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;

class OrderRequestBuilderTest extends UnitTestCase
{
    /**
     * @var ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resolverMock;

    /**
     * @var UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlMock;

    /**
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var ShopperService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shopperServiceMock;

    /**
     * @var OrderRequestBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRequestBuilder;

    public function testCreateOrderRequestBuilder()
    {
        $this->resolverMock->method('emulate')->willReturn('nl_NL');
        $this->urlMock->method('getUrl')->willReturn('testurl');
        $orderMock = $this->getOrderMock('cm_payments_creditcard');
        $orderRequest = $this->orderRequestBuilder->create($orderMock);

        $this->assertSame('000000001', $orderRequest->getPayload()['order_reference']);
        $this->assertSame(5099, $orderRequest->getPayload()['amount']);
        $this->assertSame('NL', $orderRequest->getPayload()['country']);
        $this->assertSame('nl', $orderRequest->getPayload()['language']);
    }

    public function testCreateOrderRequestBuilderWithExpiryDate()
    {
        $this->resolverMock->method('emulate')->willReturn('nl_NL');
        $this->urlMock->method('getUrl')->willReturn('testurl');

        $this->configMock->method('getOrderExpiryUnit')->willReturn('DAYS');
        $this->configMock->method('getOrderExpiryDuration')->willReturn('1');

        $orderMock = $this->getOrderMock('cm_payments_paypal');
        $orderRequest = $this->orderRequestBuilder->create($orderMock);

        $expectedResult = [
            'expire_after' => [
                'unit' => 'DAYS',
                'duration' => '1'
            ]
        ];

        $this->assertSame($expectedResult, $orderRequest->getPayload()['expiry']);
    }

    /**
     * @param string $paymentMethod
     * @return OrderInterface
     */
    protected function getOrderMock(string $paymentMethod): OrderInterface
    {
        $shippingAddressMock = $this->createConfiguredMock(
            OrderAddressInterface::class,
            [
                'getFirstname' => static::ADDRESS_DATA['firstname'],
                'getMiddlename' => static::ADDRESS_DATA['middlename'],
                'getLastname' => static::ADDRESS_DATA['lastname'],
                'getEmail' => static::ADDRESS_DATA['email_address'],
                'getStreet' => static::ADDRESS_DATA['street_address1'],
                'getCity' => static::ADDRESS_DATA['city'],
                'getRegionCode' => static::ADDRESS_DATA['region_code'],
                'getPostcode' => static::ADDRESS_DATA['postal_code'],
                'getCompany' => static::ADDRESS_DATA['company'],
                'getCountryId' => static::ADDRESS_DATA['country_code']
            ]
        );

        $orderMock = $this->createMock(Order::class);

        $paymentMock = $this->createMock(OrderPaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn($paymentMethod);

        $orderMock->method('getEntityId')->willReturn('1');
        $orderMock->method('getIncrementId')->willReturn('000000001');
        $orderMock->method('getOrderCurrencyCode')->willReturn('EUR');
        $orderMock->method('getStoreId')->willReturn(1);
        $orderMock->method('getShippingAddress')->willReturn($shippingAddressMock);
        $orderMock->method('getBillingAddress')->willReturn($shippingAddressMock);
        $orderMock->method('getGrandTotal')->willReturn(50.99);
        $orderMock->method('getPayment')->willReturn($paymentMock);

        return $orderMock;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shopperServiceMock = $this->getMockBuilder(ShopperService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderFactoryMock = $this->getMockupFactory(OrderCreate::class);

        $orderCreateRequestFactoryMock = $this->getMockupFactory(OrderCreateRequest::class);

        $mathRandomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shopperServiceMock->method('createByOrderAddress')
            ->with()->willReturn(
                new ShopperCreate([
                                      'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                                      'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
                                  ])
            );

        $orderRequestParts = [
            new OrderId(),
            new Amount(),
            new Country(),
            new Currency(),
            new Email(),
            new Language($this->resolverMock),
            new PaymentProfile($this->configMock),
            new ReturnUrls($this->urlMock),
            new Expiry($this->configMock),
            new BillingAddressKey($this->shopperServiceMock),
        ];

        $quoteRequestParts = [
            new QuoteOrderId($mathRandomMock)
        ];

        $this->orderRequestBuilder = new OrderRequestBuilder(
            $orderFactoryMock,
            $orderCreateRequestFactoryMock,
            $orderRequestParts,
            $quoteRequestParts
        );
    }
}

<?php

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Service\OrderRequestBuilder;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use CM\Payments\Client\Model\OrderCreate;

class OrderRequestBuilderTest extends UnitTestCase
{
    private $resolverMock;
    private $orderRequestBuilder;
    private $urlMock;
    private $configMock;

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

        $orderFactoryMock = $this->getMockupFactory(OrderCreate::class);

        $orderCreateRequestFactoryMock = $this->getMockupFactory(OrderCreateRequest::class);

        $this->orderRequestBuilder = new OrderRequestBuilder(
            $this->configMock,
            $this->resolverMock,
            $this->urlMock,
            $orderFactoryMock,
            $orderCreateRequestFactoryMock
        );
    }

    public function testCreateOrderRequestBuilder()
    {
        $this->resolverMock->method('emulate')->willReturn('nl_NL');
        $this->urlMock->method('getUrl')->willReturn('testurl');
        $orderMock = $this->getOrderMock();
        $orderRequest = $this->orderRequestBuilder->create($orderMock);

        $this->assertSame('001', $orderRequest->getPayload()['order_reference']);
        $this->assertSame(5099, $orderRequest->getPayload()['amount']);
        $this->assertSame('NL', $orderRequest->getPayload()['country']);
        $this->assertSame('nl', $orderRequest->getPayload()['language']);
    }

    /**
     * @return OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderMock()
    {
        $billingAddressMock = $this->getMockBuilder(OrderAddressInterface::class)
            ->getMockForAbstractClass();

        $billingAddressMock->method('getEmail')->willReturn('test@test.nl');
        $billingAddressMock->method('getCountryId')->willReturn('NL');

        $orderMock = $this->getMockBuilder(OrderInterface::class)
            ->getMockForAbstractClass();

        $orderMock->method('getEntityId')->willReturn('1');
        $orderMock->method('getIncrementId')->willReturn('001');
        $orderMock->method('getOrderCurrencyCode')->willReturn('EUR');
        $orderMock->method('getStoreId')->willReturn(1);
        $orderMock->method('getBillingAddress')->willReturn($billingAddressMock);
        $orderMock->method('getGrandTotal')->willReturn(50.99);

        return $orderMock;
    }
}

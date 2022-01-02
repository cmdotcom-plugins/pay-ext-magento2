<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Client\Model\Response\PaymentMethod;
use CM\Payments\Client\Order as ClientApiOrder;
use CM\Payments\Client\Request\OrderGetMethodsRequest;
use CM\Payments\Exception\PaymentMethodNotFoundException;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\ConfigProvider;
use CM\Payments\Service\MethodService;
use CM\Payments\Service\OrderService;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;

class MethodServiceTest extends UnitTestCase
{
    /**
     * @var MethodService
     */
    private $methodService;
    /**
     * @var CMPaymentsLogger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmPaymentsLoggerMock;
    /**
     * @var OrderGetMethodsRequest|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderGetMethodsRequestMock;
    /**
     * @var OrderService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderService;
    /**
     * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;
    /**
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;
    /**
     * @var ClientApiOrder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderClientMock;

    public function testFilterMethods()
    {
        $this->configMock->method('isPaymentMethodActive')->willReturn(true);
        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod(ConfigProvider::CODE_KLARNA)
        ];

        $cmMethods = [
            new PaymentMethod([
                'method' => 'IDEAL'
            ])
        ];

        $expectedMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL)
        ];

        $actualMethods = $this->methodService->filterMethods($magentoMethods, $cmMethods);

        $this->assertCount(1, $actualMethods);
        $this->assertSame($expectedMethods[0]->getCode(), $actualMethods[0]->getCode());
    }

    public function testFilterMethodsWithNonCMMethods()
    {
        $this->configMock->method('isPaymentMethodActive')->willReturn(true);

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod(ConfigProvider::CODE_KLARNA),
            $this->getPaymentMethod('checkmo')
        ];

        $cmMethods = [
            new PaymentMethod([
                'method' => 'IDEAL'
            ]),
        ];

        $expectedMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod('checkmo')
        ];

        $actualMethods = $this->methodService->filterMethods($magentoMethods, $cmMethods);

        $this->assertCount(2, $actualMethods);
        $this->assertSame($expectedMethods[0]->getCode(), $actualMethods[0]->getCode());
    }

    public function testGetMethodFromList()
    {
        $cmMethods = [
            new PaymentMethod(['method' => 'IDEAL']),
            new PaymentMethod(['method' => 'MEASTRO']),
        ];

        $expectedMethodCode = 'IDEAL';

        $actualMethod = $this->methodService->getMethodFromList('IDEAL', $cmMethods);

        $this->assertSame($expectedMethodCode, $actualMethod->getMethod());
    }

    public function testGetMethodFromListNotFound()
    {
        $cmMethods = [
            new PaymentMethod(['method' => 'IDEAL']),
            new PaymentMethod(['method' => 'MEASTRO']),
        ];

        $this->expectException(PaymentMethodNotFoundException::class);

        $this->methodService->getMethodFromList('NOT_FOUND', $cmMethods);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderClientMock = $this->getMockBuilder(ClientApiOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmPaymentsLoggerMock = $this->getMockBuilder(CMPaymentsLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderService = $this->getMockBuilder(OrderService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderGetMethodsRequestMock = $this->getMockupFactory(
            OrderGetMethodsRequest::class
        );

        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->methodService = new MethodService(
            $this->configMock,
            $this->orderClientMock,
            $this->orderService,
            $this->orderGetMethodsRequestMock,
            $this->eventManagerMock,
            $this->cmPaymentsLoggerMock
        );
    }

    /**
     * @param string $code
     */
    private function getPaymentMethod(string $code): PaymentMethodInterface
    {
        $paymentMethodMock = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMethodMock->method('getCode')->willReturn($code);
        $paymentMethodMock->method('getTitle')->willReturn($code);

        return $paymentMethodMock;
    }
}

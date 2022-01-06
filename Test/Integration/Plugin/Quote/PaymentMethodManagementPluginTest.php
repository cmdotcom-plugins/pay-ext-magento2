<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Cm\Payments\Test\Integration\Plugin\Quote;

use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Order;
use CM\Payments\Service\MethodService;
use CM\Payments\Service\Order\Request\Part\Amount;
use CM\Payments\Service\Order\Request\Part\Language;
use CM\Payments\Service\Order\Request\Part\OrderId;
use CM\Payments\Service\OrderRequestBuilder;
use CM\Payments\Service\OrderService;
use CM\Payments\Service\Quote\Request\Part\Amount as QuoteAmount;
use CM\Payments\Service\Quote\Request\Part\OrderId as QuoteOrderId;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class PaymentMethodManagementPluginTest extends IntegrationTestCase
{
    /**
     * @var MethodService
     */
    private $methodService;
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var ApiClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientMock;

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testAroundGetList()
    {
        $this->objectManager->addSharedInstance($this->methodService, MethodService::class);
        $this->objectManager->addSharedInstance($this->orderService, OrderService::class);

        $this->clientMock->expects($this->exactly(3))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            [],
            $this->getMethodResponse()
        );

        $subject = $this->objectManager->create(PaymentMethodManagementInterface::class);
        $magentoQuote = $this->loadQuoteById('test01');

        $actualMethods = $subject->getList(
            $magentoQuote->getId()
        );

        $magentoQuote = $this->loadQuoteById('test01');

        $this->assertSame('2287A1617D93780EF28044B98438BF2F', $magentoQuote->getCmOrderKey());
        $this->assertEquals('cm_payments', $actualMethods[0]->getCode());
        $this->assertEquals('cm_payments_ideal', $actualMethods[1]->getCode());
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 0
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testDoNothingWhenPluginIsDisabled()
    {
        $this->objectManager->addSharedInstance($this->methodService, MethodService::class);
        $this->objectManager->addSharedInstance($this->orderService, OrderService::class);

        $this->clientMock->expects($this->never())->method('execute');

        $subject = $this->objectManager->create(PaymentMethodManagementInterface::class);
        $magentoQuote = $this->loadQuoteById('test01');

        $subject->getList(
            $magentoQuote->getId()
        );
    }

    /**
     * Setup of Method Service
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ApiClientInterface::class);
        $orderClient = $this->objectManager->create(Order::class, ['apiClient' => $this->clientMock]);
        $orderRequestBuilder = $this->objectManager->create(OrderRequestBuilder::class, [
            'orderRequestParts' => [
                $this->objectManager->create(OrderId::class),
                $this->objectManager->create(Amount::class),
                $this->objectManager->create(\CM\Payments\Service\Order\Item\Request\Part\Currency::class),
                $this->objectManager->create(Language::class),
            ],
            'quoteRequestParts' => [
                $this->objectManager->create(QuoteOrderId::class),
                $this->objectManager->create(QuoteAmount::class),
            ]
        ]);

        $this->orderService = $this->objectManager->create(OrderServiceInterface::class, [
            'orderRequestBuilder' => $orderRequestBuilder,
            'orderClient' => $orderClient
        ]);

        $this->methodService = $this->objectManager->create(
            MethodService::class,
            [
                'orderService' => $this->orderService,
                'orderClient' => $orderClient
            ]
        );
    }

    /**
     * @return array[]
     */
    private function getMethodResponse()
    {
        return [
            [
                'method' => 'IDEAL',
            ],
            [
                'method' => 'BANCONTACT',
            ]
        ];
    }
}

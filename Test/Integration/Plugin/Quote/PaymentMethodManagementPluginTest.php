<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Cm\Payments\Test\Integration\Plugin\Quote;

use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Order;
use CM\Payments\Model\ConfigProvider;
use CM\Payments\Plugin\Quote\PaymentMethodManagementPlugin;
use CM\Payments\Service\MethodService;
use CM\Payments\Service\Order\Request\Part\Amount;
use CM\Payments\Service\Order\Request\Part\Language;
use CM\Payments\Service\Order\Request\Part\OrderId;
use CM\Payments\Service\OrderRequestBuilder;
use CM\Payments\Service\Quote\Request\Part\Amount as QuoteAmount;
use CM\Payments\Service\Quote\Request\Part\OrderId as QuoteOrderId;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class PaymentMethodManagementPluginTest extends IntegrationTestCase
{
    /**
     * @var MethodService
     */
    private $methodService;
    /**
     * @var ApiClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientMock;

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testAfterGetList()
    {
        /** @var PaymentMethodManagementPlugin $addMethodsAdditionalDataPayment */
        $addMethodsAdditionalDataPayment = $this->objectManager->create(
            PaymentMethodManagementPlugin::class,
            ['methodService' => $this->methodService]
        );

        $this->clientMock->expects($this->exactly(3))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            [],
            [
                [
                    'method' => 'IDEAL',
                ],
                [
                    'method' => 'KLARNA',
                ]
            ]
        );

        $subject = $this->objectManager->create(PaymentMethodManagementInterface::class);
        $magentoQuote = $this->loadQuoteById('test01');
        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE),
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod('checkmo')
        ];

        $actualMethods = $addMethodsAdditionalDataPayment->afterGetList($subject, $magentoMethods, $magentoQuote->getId());

        $this->assertEquals('cm_payments', $actualMethods[0]->getCode());
        $this->assertEquals('cm_payments_ideal', $actualMethods[1]->getCode());

        $magentoQuote = $this->loadQuoteById('test01');

        $this->assertSame('2287A1617D93780EF28044B98438BF2F', $magentoQuote->getCmOrderKey());
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

        $orderService = $this->objectManager->create(OrderServiceInterface::class, [
            'orderRequestBuilder' => $orderRequestBuilder,
            'orderClient' => $orderClient
        ]);

        $this->methodService = $this->objectManager->create(
            MethodService::class,
            [
                'orderService' => $orderService,
                'orderClient' => $orderClient
            ]
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

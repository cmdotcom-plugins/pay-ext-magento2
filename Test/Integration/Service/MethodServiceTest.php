<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Model\CMPaymentUrlFactory;
use CM\Payments\Client\Order;
use CM\Payments\Model\ConfigProvider;
use CM\Payments\Service\Method\Ideal;
use CM\Payments\Service\MethodService;
use CM\Payments\Service\Order\Item\Request\Part\Currency;
use CM\Payments\Service\Order\Request\Part\Amount;
use CM\Payments\Service\Quote\Request\Part\Amount as QuoteAmount;
use CM\Payments\Service\Order\Request\Part\Language;
use CM\Payments\Service\Order\Request\Part\OrderId;
use CM\Payments\Service\Quote\Request\Part\OrderId as QuoteOrderId;
use CM\Payments\Service\OrderRequestBuilder;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Exception;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;

class MethodServiceTest extends IntegrationTestCase
{
    /**
     * @var ApiClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var MethodServiceInterface
     */
    private $methodService;

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 0
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetMethodsByQuote()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod(ConfigProvider::CODE_BANCONTACT)
        ];

        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $idealMethod = $actualPaymentMethods[0];
        $banContactMethod = $actualPaymentMethods[1];

        $this->assertEquals(2, count($actualPaymentMethods));
        $this->assertEquals('cm_payments_ideal', $idealMethod->getCode());
        $this->assertEquals('cm_payments_bancontact', $banContactMethod->getCode());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 0
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testBanContactPaymentMethod()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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
                    'ideal_details' => [
                        'issuers' => [
                            [
                                'id' => 'BUNQNL2A',
                                'name' => 'bunq'
                            ],
                            [
                                'id' => 'ASNBNL21',
                                'name' => 'ASN Bank'
                            ]
                        ],
                    ],
                ],
                [
                    'method' => 'BANCONTACT',
                ],
                [
                    'method' => 'PAYPAL',
                ]
            ]
        );

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod(ConfigProvider::CODE_BANCONTACT)
        ];
        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $banContactMethod = $actualPaymentMethods[1];
        $this->assertEquals(2, count($actualPaymentMethods));
        $this->assertEquals('cm_payments_bancontact', $banContactMethod->getCode());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetAvailablePaymentMethods()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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
                    'method' => 'BANCONTACT',
                ],
                [
                    'method' => 'PAYPAL_EXPRESS_CHECKOUT',
                ]
            ]
        );

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod(ConfigProvider::CODE_CREDIT_CARD),
            $this->getPaymentMethod(ConfigProvider::CODE_PAYPAL),
            $this->getPaymentMethod(ConfigProvider::CODE_BANCONTACT)
        ];

        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $this->assertEquals(3, count($actualPaymentMethods));
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testCreditCardPaymentsMethods()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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
                    'method' => 'MASTERCARD',
                ]
            ]
        );

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod(ConfigProvider::CODE_CREDIT_CARD),
        ];

        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $this->assertEquals(1, count($actualPaymentMethods));
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 0
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testInactiveMagentoIdealSetting()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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

        $magentoMethods = [];

        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $this->assertEquals(0, count($actualPaymentMethods));
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testInactiveCMIdealSetting()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_BANCONTACT)
        ];
        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $method = $actualPaymentMethods[0];
        $this->assertEquals(1, count($actualPaymentMethods));
        $this->assertNotEquals('cm_payments_ideal', $method->getCode());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testCmPaymentsRedirectPaymentMethod()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_CM_PAYMENTS_MENU)
        ];
        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $method = $actualPaymentMethods[0];
        $this->assertEquals(1, count($actualPaymentMethods));
        $this->assertEquals('cm_payments', $method->getCode());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_applepay/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testApplePayRedirectPaymentMethod()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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
                    'method' => 'APPLE_PAY',
                ]
            ]
        );

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_APPLEPAY)
        ];
        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $method = $actualPaymentMethods[0];
        $this->assertEquals(1, count($actualPaymentMethods));
        $this->assertEquals('cm_payments_applepay', $method->getCode());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_giftcard/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGiftcardRedirectPaymentMethod()
    {
        $magentoQuote = $this->loadQuoteById('test01');

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
                    'method' => 'FASHION_GIFTCARD',
                ]
            ]
        );

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_GIFTCARD)
        ];
        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $method = $actualPaymentMethods[0];
        $this->assertEquals(1, count($actualPaymentMethods));
        $this->assertEquals('cm_payments_giftcard', $method->getCode());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testEmptyCmOrderKey()
    {
        $magentoQuote = $this->loadQuoteById('test01');

        $this->clientMock->expects($this->exactly(1))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ]
        );

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_CM_PAYMENTS_MENU)
        ];
        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $method = $actualPaymentMethods[0];
        $this->assertEquals(1, count($actualPaymentMethods));
        $this->assertEquals('cm_payments', $method->getCode());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testCmApiErrorShouldContinueAndReturnMagentoMethods()
    {
        $magentoQuote = $this->loadQuoteById('test01');

        $this->clientMock->expects($this->exactly(1))->method('execute')->willThrowException(new Exception());

        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_CM_PAYMENTS_MENU),
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod(ConfigProvider::CODE_BANCONTACT),
        ];
        $actualPaymentMethods = $this->methodService->getMethodsByQuote($magentoQuote, $magentoMethods);

        $method = $actualPaymentMethods[0];
        $this->assertEquals(2, count($actualPaymentMethods));
        $this->assertEquals('cm_payments', $method->getCode());
        // When api throws an error we don't have ideal issuer so we hide this method on the payment page
        $this->assertArrayNotHasKey(1, $actualPaymentMethods);
        $this->assertNotEquals('cm_payments_ideal', $actualPaymentMethods[2]->getCode());
    }

    /**
     * Setup of test
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
                $this->objectManager->create(Currency::class),
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

    /**
     * @return array[]
     */
    private function getMethodResponse()
    {
        return [
            [
                'method' => 'IDEAL',
                'ideal_details' => [
                    'issuers' => [
                        [
                            'id' => 'BUNQNL2A',
                            'name' => 'bunq'
                        ],
                        [
                            'id' => 'ASNBNL21',
                            'name' => 'ASN Bank'
                        ]
                    ],
                ],
            ],
            [
                'method' => 'BANCONTACT',
            ]
        ];
    }
}

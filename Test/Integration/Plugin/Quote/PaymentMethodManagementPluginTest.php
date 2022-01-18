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
use CM\Payments\Service\Quote\Request\Part\Amount as QuoteAmount;
use CM\Payments\Service\Quote\Request\Part\BillingAddressKey as QuoteBillingAddressKey;
use CM\Payments\Service\Quote\Request\Part\Country as QuoteCountry;
use CM\Payments\Service\Quote\Request\Part\Currency as QuoteCurrency;
use CM\Payments\Service\Quote\Request\Part\Email as QuoteEmail;
use CM\Payments\Service\Quote\Request\Part\Expiry as QuoteExpiry;
use CM\Payments\Service\Quote\Request\Part\Language as QuoteLanguage;
use CM\Payments\Service\Quote\Request\Part\OrderId as QuoteOrderId;
use CM\Payments\Service\Quote\Request\Part\PaymentProfile as QuotePaymentProfile;
use CM\Payments\Service\Quote\Request\Part\ReturnUrls as QuoteReturnUrls;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class AddMethodsAdditionalDataPaymentTest extends IntegrationTestCase
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

        $this->clientMock->expects($this->exactly(1))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ],
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
        $magentoMethods = [
            $this->getPaymentMethod(ConfigProvider::CODE_IDEAL),
            $this->getPaymentMethod(ConfigProvider::CODE_KLARNA),
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
        $orderClient = $this->objectManager->create(
            Order::class,
            [
                'apiClient' => $this->clientMock,
            ]
        );

        $orderRequestBuilder = $this->objectManager->create(OrderRequestBuilder::class, [
            'orderRequestParts' => [
                $this->objectManager->create(OrderId::class),
                $this->objectManager->create(Amount::class),
                $this->objectManager->create(Currency::class),
                $this->objectManager->create(Language::class),
                $this->objectManager->create(Country::class),
                $this->objectManager->create(PaymentProfile::class),
                $this->objectManager->create(Email::class),
                $this->objectManager->create(ReturnUrls::class),
                $this->objectManager->create(Expiry::class),
                $this->objectManager->create(
                    BillingAddressKey::class,
                    [
                        'shopperService' => $this->shopperService
                    ]
                ),
            ],
            'quoteRequestParts' => [
                $this->objectManager->create(QuoteOrderId::class),
                $this->objectManager->create(QuoteAmount::class),
                $this->objectManager->create(QuoteCurrency::class),
                $this->objectManager->create(QuoteLanguage::class),
                $this->objectManager->create(QuoteCountry::class),
                $this->objectManager->create(QuotePaymentProfile::class),
                $this->objectManager->create(QuoteEmail::class),
                $this->objectManager->create(QuoteReturnUrls::class),
                $this->objectManager->create(QuoteExpiry::class),
                $this->objectManager->create(
                    QuoteBillingAddressKey::class,
                    [
                        'shopperService' => $this->shopperService
                    ]
                ),
            ]
        ]);

        $this->orderService = $this->objectManager->create(OrderServiceInterface::class, [
            'orderRequestBuilder' =>  $orderRequestBuilder,
            'orderClient' => $orderClient
        ]);

        $this->methodService = $this->objectManager->create(
            MethodService::class,
            [
                'orderClient' => $orderClient,
                'orderService' => $this->orderService
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

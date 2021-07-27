<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Api\Data\IssuerInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Model\CMPaymentUrlFactory;
use CM\Payments\Client\Order;
use CM\Payments\Service\Method\Ideal;
use CM\Payments\Service\MethodService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Checkout\Model\PaymentDetails;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
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
    public function testAddMethodAdditionalIdealData()
    {
        $magentoQuote = $this->loadQuoteById('test01');

        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            $this->getMethodResponse()
        );

        $paymentDetails = $this->getPaymentMethodList($magentoQuote);
        $actualPaymentDetails = $this->methodService->addMethodAdditionalData($magentoQuote, $paymentDetails);

        $methods = $actualPaymentDetails->getPaymentMethods();
        $issuers = $actualPaymentDetails->getExtensionAttributes()->getCmPaymentsIdeal()->getIssuers();

        $idealMethod = $methods[0];
        $banContactMethod = $methods[1];

        $this->assertEquals(2, count($issuers));
        $this->assertContainsOnlyInstancesOf(IssuerInterface::class, $issuers);

        $this->assertEquals(2, count($methods));
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

        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            $this->getMethodResponse()
        );

        $paymentDetails = $this->getPaymentMethodList($magentoQuote);
        $actualPaymentDetails = $this->methodService->addMethodAdditionalData($magentoQuote, $paymentDetails);

        $methods = $actualPaymentDetails->getPaymentMethods();

        $banContactMethod = $methods[1];
        $this->assertEquals(2, count($methods));
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

        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            $this->getMethodResponse()
        );

        $paymentDetails = $this->getPaymentMethodList($magentoQuote);
        $actualPaymentDetails = $this->methodService->addMethodAdditionalData($magentoQuote, $paymentDetails);
        $actualPaymentMethods = $actualPaymentDetails->getPaymentMethods();

        $this->assertEquals(3, count($actualPaymentMethods));
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

        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            $this->getMethodResponse()
        );

        $paymentDetails = $this->getPaymentMethodList($magentoQuote);

        $actualPaymentDetails = $this->methodService->addMethodAdditionalData($magentoQuote, $paymentDetails);
        $actualPaymentMethods = $actualPaymentDetails->getPaymentMethods();

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

        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            $this->getMethodResponse()
        );

        $paymentDetails = $this->getPaymentMethodList($magentoQuote);
        $actualPaymentDetails = $this->methodService->addMethodAdditionalData($magentoQuote, $paymentDetails);
        $actualPaymentMethods = $actualPaymentDetails->getPaymentMethods();

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

        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            $this->getMethodResponse()
        );

        $paymentDetails = $this->getPaymentMethodList($magentoQuote);
        $actualPaymentDetails = $this->methodService->addMethodAdditionalData($magentoQuote, $paymentDetails);
        $actualPaymentMethods = $actualPaymentDetails->getPaymentMethods();

        $method = $actualPaymentMethods[0];
        $this->assertEquals(1, count($actualPaymentMethods));
        $this->assertEquals('cm_payments', $method->getCode());
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

        $this->clientMock->expects($this->exactly(1))->method('execute')->willReturn(
            [
                'order_key' => '',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ]
        );

        $paymentDetails = $this->getPaymentMethodList($magentoQuote);
        $actualPaymentDetails = $this->methodService->addMethodAdditionalData($magentoQuote, $paymentDetails);
        $actualPaymentMethods = $actualPaymentDetails->getPaymentMethods();

        $method = $actualPaymentMethods[0];
        $this->assertEquals(1, count($actualPaymentMethods));
        $this->assertEquals('cm_payments', $method->getCode());
    }

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

        $this->methodService = $this->objectManager->create(
            MethodService::class,
            [
                'orderClient' => $orderClient,
                'methods' => [
                    $this->objectManager->create(Ideal::class)
                ],
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

    /**
     * @param string $orderId
     * @return CartInterface
     */
    private function loadQuoteById($orderId)
    {
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $orderId, 'eq')->create();

        $orderList = $quoteRepository->getList($searchCriteria)->getItems();

        return array_shift($orderList);
    }

    /**
     * @param CartInterface $magentoQuote
     * @return PaymentDetails
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getPaymentMethodList(CartInterface $magentoQuote): PaymentDetails
    {
        $paymentMethodManagement = $this->objectManager->create(PaymentMethodManagementInterface::class);
        $paymentDetails = $this->objectManager->create(PaymentDetails::class);
        $paymentDetails->setPaymentMethods($paymentMethodManagement->getList($magentoQuote->getId()));

        return $paymentDetails;
    }
}

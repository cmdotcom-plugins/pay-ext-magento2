<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Cm\Payments\Test\Integration\Plugin\Checkout\Model;

use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\ShopperServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Order;
use CM\Payments\Client\Shopper;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Plugin\Checkout\Model\AddMethodsAdditionalDataPayment;
use CM\Payments\Service\Method\Ideal;
use CM\Payments\Service\MethodService;
use CM\Payments\Service\Order\Address\Request\Part\Address as OrderAddressFull;
use CM\Payments\Service\Order\Address\Request\Part\DateOfBirth as OrderAddressDateOfBirth;
use CM\Payments\Service\Order\Address\Request\Part\Email as OrderAddressEmail;
use CM\Payments\Service\Order\Address\Request\Part\Gender as OrderAddressGender;
use CM\Payments\Service\Order\Address\Request\Part\Name as OrderAddressName;
use CM\Payments\Service\Order\Address\Request\Part\PhoneNumber as OrderAddressPhoneNumber;
use CM\Payments\Service\Order\Address\Request\Part\ShopperId as OrderAddressShopperId;
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
use CM\Payments\Service\Quote\Address\Request\Part\Address as QuoteAddressFull;
use CM\Payments\Service\Quote\Address\Request\Part\DateOfBirth as QuoteAddressDateOfBirth;
use CM\Payments\Service\Quote\Address\Request\Part\Email as QuoteAddressEmail;
use CM\Payments\Service\Quote\Address\Request\Part\Gender as QuoteAddressGender;
use CM\Payments\Service\Quote\Address\Request\Part\Name as QuoteAddressName;
use CM\Payments\Service\Quote\Address\Request\Part\PhoneNumber as QuoteAddressPhoneNumber;
use CM\Payments\Service\Quote\Address\Request\Part\ShopperId as QuoteAddressShopperId;
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
use CM\Payments\Service\ShopperRequestBuilder;
use CM\Payments\Service\ShopperService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Model\PaymentMethod;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;

class AddMethodsAdditionalDataPaymentTest extends IntegrationTestCase
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
     * @var ShopperServiceInterface
     */
    private $shopperService;

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 0
     */
    public function testDoNothingWhenModuleIsDisabled()
    {
        /** @var AddMethodsAdditionalDataPayment $addMethodsAdditionalDataPayment */
        $addMethodsAdditionalDataPayment = $this->objectManager->create(AddMethodsAdditionalDataPayment::class);

        /** @var PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->objectManager->create(PaymentDetailsInterface::class);

        $subject = $this->objectManager->create(PaymentInformationManagementInterface::class);
        $addMethodsAdditionalDataPayment->afterGetPaymentInformation($subject, $paymentDetails, '1');
    }

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
    public function testAddMethodsAdditionalDataPayment()
    {
        /** @var AddMethodsAdditionalDataPayment $addMethodsAdditionalDataPayment */
        $addMethodsAdditionalDataPayment = $this->objectManager->create(
            AddMethodsAdditionalDataPayment::class,
            ['methodService' => $this->methodService]
        );

        /** @var PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->objectManager->create(PaymentDetailsInterface::class);
        $idealMethod = $this->objectManager->create(
            PaymentMethod::class,
            ['code' => 'cm_payments_ideal', 'title' => 'Ideal', 'storeId' => 1, 'isActive' => true]
        );
        $cmPaymentsMethods = $this->objectManager->create(
            PaymentMethod::class,
            ['code' => 'cm_payments', 'title' => 'Redirect', 'storeId' => 1, 'isActive' => true]
        );
        $paymentDetails->setPaymentMethods([
            $cmPaymentsMethods,
            $idealMethod
        ]);

        $magentoQuote = $this->loadQuoteById('test01');
        $subject = $this->objectManager->create(PaymentInformationManagementInterface::class);

        $this->clientMock->expects($this->exactly(4))->method('execute')->willReturnOnConsecutiveCalls(
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

        $paymentDetails = $addMethodsAdditionalDataPayment
            ->afterGetPaymentInformation($subject, $paymentDetails, $magentoQuote->getId());

        $this->assertEquals('cm_payments', $paymentDetails->getPaymentMethods()[0]->getCode());
        $this->assertEquals('cm_payments_ideal', $paymentDetails->getPaymentMethods()[1]->getCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ApiClientInterface::class);
        $this->setupShopperService();
        $this->setupMethodService();
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
     * Setup of Shopper Service
     */
    private function setupShopperService()
    {
        $shopperClient = $this->objectManager->create(
            Shopper::class,
            [
                'apiClient' => $this->clientMock,
            ]
        );

        $shopperRequestBuilder = $this->objectManager->create(ShopperRequestBuilder::class, [
            'orderAddressRequestParts' => [
                $this->objectManager->create(OrderAddressShopperId::class),
                $this->objectManager->create(OrderAddressName::class),
                $this->objectManager->create(OrderAddressFull::class),
                $this->objectManager->create(OrderAddressEmail::class),
                $this->objectManager->create(OrderAddressGender::class),
                $this->objectManager->create(OrderAddressDateOfBirth::class),
                $this->objectManager->create(OrderAddressPhoneNumber::class),
            ],
            'quoteAddressRequestParts' => [
                $this->objectManager->create(QuoteAddressShopperId::class),
                $this->objectManager->create(QuoteAddressName::class),
                $this->objectManager->create(QuoteAddressFull::class),
                $this->objectManager->create(QuoteAddressEmail::class),
                $this->objectManager->create(QuoteAddressGender::class),
                $this->objectManager->create(QuoteAddressDateOfBirth::class),
                $this->objectManager->create(QuoteAddressPhoneNumber::class),
            ]
        ]);

        $this->shopperService = $this->objectManager->create(
            ShopperService::class,
            [
                'shopperClient' => $shopperClient,
                'shopperRequestBuilder' => $shopperRequestBuilder,
                'eventManager' => $this->objectManager->create(ManagerInterface::class),
                'cmPaymentsLogger' => $this->objectManager->create(CMPaymentsLogger::class, ['name' => 'CMPayments'])
            ]
        );
    }

    /**
     * Setup of Method Service
     */
    private function setupMethodService()
    {
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

        $this->methodService = $this->objectManager->create(
            MethodService::class,
            [
                'orderClient' => $orderClient,
                'methods' => [
                    $this->objectManager->create(Ideal::class)
                ],
                'orderRequestBuilder' => $orderRequestBuilder
            ]
        );
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Cm\Payments\Test\Integration\Plugin\Checkout\Model;

use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Order;
use CM\Payments\Plugin\Checkout\Model\AddMethodsAdditionalDataShipping;
use CM\Payments\Service\Method\Ideal;
use CM\Payments\Service\MethodService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Model\PaymentMethod;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\MockObject;

class AddMethodsAdditionalDataShippingTest extends IntegrationTestCase
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
     * @magentoConfigFixture default_store cm_payments/general/enabled 0
     */
    public function testDoNothingWhenModuleIsDisabled()
    {
        /** @var AddMethodsAdditionalDataShipping $addMethodsAdditionalDataPayment */
        $addMethodsAdditionalDataShipping = $this->objectManager->create(AddMethodsAdditionalDataShipping::class);

        /** @var PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->objectManager->create(PaymentDetailsInterface::class);

        $subject = $this->objectManager->create(ShippingInformationManagementInterface::class);
        $addressInformation = $this->objectManager->create(ShippingInformationInterface::class);
        $addMethodsAdditionalDataShipping
            ->afterSaveAddressInformation($subject, $paymentDetails, '1', $addressInformation);
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
    public function testAddMethodsAdditionalDataShipping()
    {
        /** @var AddMethodsAdditionalDataShipping $addMethodsAdditionalDataShipping */
        $addMethodsAdditionalDataShipping = $this->objectManager->create(
            AddMethodsAdditionalDataShipping::class,
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
        $subject = $this->objectManager->create(ShippingInformationManagementInterface::class);

        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ],
            $this->getMethodResponse()
        );

        $addressInformation = $this->objectManager->create(ShippingInformationInterface::class);
        $paymentDetails = $addMethodsAdditionalDataShipping
            ->afterSaveAddressInformation($subject, $paymentDetails, $magentoQuote->getId(), $addressInformation);

        $this->assertEquals('cm_payments', $paymentDetails->getPaymentMethods()[0]->getCode());
        $this->assertEquals('cm_payments_ideal', $paymentDetails->getPaymentMethods()[1]->getCode());
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
}

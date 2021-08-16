<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Api\Model\Data\OrderInterfaceFactory;
use CM\Payments\Api\Model\Domain\CMOrderInterfaceFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Api\Service\ShopperServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Order;
use CM\Payments\Client\Shopper;
use CM\Payments\Exception\EmptyOrderKeyException;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Service\Order\Address\Request\Part\Address as OrderAddressFull;
use CM\Payments\Service\Order\Address\Request\Part\DateOfBirth as OrderAddressDateOfBirth;
use CM\Payments\Service\Order\Address\Request\Part\Email as OrderAddressEmail;
use CM\Payments\Service\Order\Address\Request\Part\Gender as OrderAddressGender;
use CM\Payments\Service\Order\Address\Request\Part\Name as OrderAddressName;
use CM\Payments\Service\Order\Address\Request\Part\PhoneNumber as OrderAddressPhoneNumber;
use CM\Payments\Service\Order\Address\Request\Part\ShopperId as OrderAddressShopperId;
use CM\Payments\Service\Order\Item\Request\Part\Amount as OrderItemAmount;
use CM\Payments\Service\Order\Item\Request\Part\Currency as OrderItemCurrency;
use CM\Payments\Service\Order\Item\Request\Part\Description as OrderItemDescription;
use CM\Payments\Service\Order\Item\Request\Part\ItemId as OrderItemId;
use CM\Payments\Service\Order\Item\Request\Part\Name as OrderItemName;
use CM\Payments\Service\Order\Item\Request\Part\Quantity as OrderItemQuantity;
use CM\Payments\Service\Order\Item\Request\Part\Sku as OrderItemSku;
use CM\Payments\Service\Order\Item\Request\Part\Type as OrderItemType;
use CM\Payments\Service\Order\Item\Request\Part\UnitAmount as OrderItemUnitAmount;
use CM\Payments\Service\Order\Item\Request\Part\VatAmount as OrderItemVatAmount;
use CM\Payments\Service\Order\Item\Request\Part\VatRate as OrderItemVatRate;
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
use CM\Payments\Service\OrderItemsRequestBuilder;
use CM\Payments\Service\OrderRequestBuilder;
use CM\Payments\Service\OrderService;
use CM\Payments\Service\Quote\Address\Request\Part\Address as QuoteAddressFull;
use CM\Payments\Service\Quote\Address\Request\Part\DateOfBirth as QuoteAddressDateOfBirth;
use CM\Payments\Service\Quote\Address\Request\Part\Email as QuoteAddressEmail;
use CM\Payments\Service\Quote\Address\Request\Part\Gender as QuoteAddressGender;
use CM\Payments\Service\Quote\Address\Request\Part\Name as QuoteAddressName;
use CM\Payments\Service\Quote\Address\Request\Part\PhoneNumber as QuoteAddressPhoneNumber;
use CM\Payments\Service\Quote\Address\Request\Part\ShopperId as QuoteAddressShopperId;
use CM\Payments\Service\Quote\Item\Request\Part\Amount as QuoteItemAmount;
use CM\Payments\Service\Quote\Item\Request\Part\Currency as QuoteItemCurrency;
use CM\Payments\Service\Quote\Item\Request\Part\Description as QuoteItemDescription;
use CM\Payments\Service\Quote\Item\Request\Part\ItemId as QuoteItemId;
use CM\Payments\Service\Quote\Item\Request\Part\Name as QuoteItemName;
use CM\Payments\Service\Quote\Item\Request\Part\Quantity as QuoteItemQuantity;
use CM\Payments\Service\Quote\Item\Request\Part\Sku as QuoteItemSku;
use CM\Payments\Service\Quote\Item\Request\Part\Type as QuoteItemType;
use CM\Payments\Service\Quote\Item\Request\Part\UnitAmount as QuoteItemUnitAmount;
use CM\Payments\Service\Quote\Item\Request\Part\VatAmount as QuoteItemVatAmount;
use CM\Payments\Service\Quote\Item\Request\Part\VatRate as QuoteItemVatRate;
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
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use PHPUnit\Framework\MockObject\MockObject;

class OrderServiceTest extends IntegrationTestCase
{
    /**
     * @var ApiClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var ShopperServiceInterface
     */
    private $shopperService;

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateOrder()
    {
        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ],
            [
                'order_key' => '0287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ]
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $order = $this->orderService->create($magentoOrder->getId());
        $this->assertSame(
        //phpcs:ignore
            'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
            $order->getUrl()
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateOrderRequestException()
    {
        $this->expectException(RequestException::class);
        $this->expectException(LocalizedException::class);

        $this->clientMock->expects($this->any())->method('execute')
            ->willThrowException(
                new RequestException(
                    json_encode(['messages' => 'Property country must match \"[A-Z]{2}\"']),
                    new Request('GET', 'test'),
                    new Response(400)
                )
            );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $this->orderService->create($magentoOrder->getId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateOrderEmptyOrderKey()
    {
        $this->expectException(EmptyOrderKeyException::class);

        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ],
            []
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $this->orderService->create($magentoOrder->getId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSaveOrderReferenceInDatabase()
    {
        $this->clientMock->expects($this->exactly(2))->method('execute')->willReturnOnConsecutiveCalls(
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ],
            [
                'order_key' => '2287A1617D93780EF28044B98438BF2F',
                //phpcs:ignore
                'url' => 'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
                'expires_on' => '2021-07-12T08:10:57Z'
            ]
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $this->orderService->create($magentoOrder->getId());

        /** @var OrderRepositoryInterface $cmOrderRepository */
        $cmOrderRepository = $this->objectManager->create(CMOrderRepositoryInterface::class);

        $result = $cmOrderRepository->getByOrderKey('2287A1617D93780EF28044B98438BF2F');

        $this->assertSame((int)$magentoOrder->getId(), $result->getOrderId());
    }

    /**
     * Setup of test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ApiClientInterface::class);
        $this->setupShopperService();
        $this->setupOrderService();
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
     * Setup of Order Service
     */
    private function setupOrderService()
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

        $orderItemsRequestBuilder = $this->objectManager->create(OrderItemsRequestBuilder::class, [
            'orderItemRequestParts' => [
                $this->objectManager->create(OrderItemId::class),
                $this->objectManager->create(OrderItemSku::class),
                $this->objectManager->create(OrderItemName::class),
                $this->objectManager->create(OrderItemDescription::class),
                $this->objectManager->create(OrderItemType::class),
                $this->objectManager->create(OrderItemQuantity::class),
                $this->objectManager->create(OrderItemCurrency::class),
                $this->objectManager->create(OrderItemUnitAmount::class),
                $this->objectManager->create(OrderItemAmount::class),
                $this->objectManager->create(OrderItemVatAmount::class),
                $this->objectManager->create(OrderItemVatRate::class)
            ],
            'quoteItemRequestParts' => [
                $this->objectManager->create(QuoteItemId::class),
                $this->objectManager->create(QuoteItemSku::class),
                $this->objectManager->create(QuoteItemName::class),
                $this->objectManager->create(QuoteItemDescription::class),
                $this->objectManager->create(QuoteItemType::class),
                $this->objectManager->create(QuoteItemQuantity::class),
                $this->objectManager->create(QuoteItemCurrency::class),
                $this->objectManager->create(QuoteItemUnitAmount::class),
                $this->objectManager->create(QuoteItemAmount::class),
                $this->objectManager->create(QuoteItemVatAmount::class),
                $this->objectManager->create(QuoteItemVatRate::class)
            ]
        ]);

        $this->orderService = $this->objectManager->create(
            OrderService::class,
            [
                'orderRepository' => $this->objectManager->create(OrderRepository::class),
                'orderClient' => $orderClient,
                'orderInterfaceFactory' => $this->objectManager->create(OrderInterfaceFactory::class),
                'cmOrderRepository' => $this->objectManager->create(\CM\Payments\Model\OrderRepository::class),
                'orderRequestBuilder' => $orderRequestBuilder,
                'orderItemsRequestBuilder' => $orderItemsRequestBuilder,
                'cmOrderInterfaceFactory' => $this->objectManager->create(CMOrderInterfaceFactory::class),
                'eventManager' => $this->objectManager->create(ManagerInterface::class),
                'cmPaymentsLogger' => $this->objectManager->create(CMPaymentsLogger::class, ['name' => 'CMPayments'])
            ]
        );
    }
}

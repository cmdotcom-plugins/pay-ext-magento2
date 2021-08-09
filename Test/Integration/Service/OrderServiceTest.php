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
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Order;
use CM\Payments\Exception\EmptyOrderKeyException;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Service\Order\Request\Part\Amount;
use CM\Payments\Service\Order\Request\Part\Country;
use CM\Payments\Service\Order\Request\Part\Currency;
use CM\Payments\Service\Order\Request\Part\Email;
use CM\Payments\Service\Order\Request\Part\Expiry;
use CM\Payments\Service\Order\Request\Part\Language;
use CM\Payments\Service\Order\Request\Part\OrderId;
use CM\Payments\Service\Order\Request\Part\PaymentProfile;
use CM\Payments\Service\Order\Request\Part\ReturnUrls;
use CM\Payments\Service\Quote\Request\Part\Amount as QuoteAmount;
use CM\Payments\Service\Quote\Request\Part\Country as QuoteCountry;
use CM\Payments\Service\Quote\Request\Part\Currency as QuoteCurrency;
use CM\Payments\Service\Quote\Request\Part\Email as QuoteEmail;
use CM\Payments\Service\Quote\Request\Part\Language as QuoteLanguage;
use CM\Payments\Service\Quote\Request\Part\OrderId as QuoteOrderId;
use CM\Payments\Service\Quote\Request\Part\PaymentProfile as QuotePaymentProfile;
use CM\Payments\Service\Quote\Request\Part\ReturnUrls as QuoteReturnUrls;
use CM\Payments\Service\OrderRequestBuilder;
use CM\Payments\Service\OrderService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
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
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateOrder()
    {
        $this->clientMock->expects($this->once())->method('execute')->willReturn(
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
     *
     * @param $orderId
     * @return OrderInterface
     */
    private function loadOrderById($orderId)
    {
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $builder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $builder->addFilter('increment_id', $orderId, 'eq')->create();

        $orderList = $repository->getList($searchCriteria)->getItems();

        return array_shift($orderList);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateOrderRequestException()
    {
        $this->expectException(RequestException::class);

        $this->clientMock->expects($this->once())->method('execute')->willThrowException(
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

        $this->clientMock->expects($this->once())->method('execute')->willReturn([]);

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $this->orderService->create($magentoOrder->getId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSaveOrderReferenceInDatabase()
    {
        $this->clientMock->expects($this->once())->method('execute')->willReturn(
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
                $this->objectManager->create(Expiry::class),
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
                'cmOrderInterfaceFactory' => $this->objectManager->create(CMOrderInterfaceFactory::class),
                'cmPaymentsLogger' => $this->objectManager->create(CMPaymentsLogger::class, ['name' => 'CMPayments'])
            ]
        );
    }
}

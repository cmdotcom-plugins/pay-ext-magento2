<?php

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Model\CMPaymentFactory;
use CM\Payments\Client\Model\CMPaymentUrlFactory;
use CM\Payments\Service\PaymentRequestBuilder;
use CM\Payments\Service\PaymentService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use PHPUnit\Framework\MockObject\MockObject;

class PaymentServiceTest extends IntegrationTestCase
{
    /**
     * @var ApiClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreatePayment()
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

        $order = $this->paymentService->create($magentoOrder->getId());
        $this->assertSame(
        //phpcs:ignore
            'https://testsecure.docdatapayments.com/ps/menu?merchant_name=itonomy_b_v&client_language=NL&payment_cluster_key=0287A1617D93780EF28044B98438BF2F',
            $order->getUrl()
        );
    }

    /**
     * @param string $orderId
     * @return OrderInterface
     */
    private function loadOrderById($orderId)
    {
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $orderId, 'eq')->create();

        $orderList = $orderRepository->getList($searchCriteria)->getItems();

        return array_shift($orderList);
    }

    /**
     * @param OrderInterface $magentoOrder
     * @return OrderInterface
     */
    private function addCurrencyToOrder(OrderInterface $magentoOrder): OrderInterface
    {
        /** @var OrderInterface $magentoOrder */
        $magentoOrder
            ->setOrderCurrencyCode('USD')
            ->setBaseCurrencyCode('USD');

        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        return $magentoOrder;
    }

    /**
     *
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

        $this->paymentService = $this->objectManager->create(
            PaymentService::class,
            [
                'orderRepository' => $this->objectManager->create(OrderRepository::class),
                'apiClient' => $this->clientMock,
                'paymentRequestBuilder' => $this->objectManager->create(PaymentRequestBuilder::class),
                'cmPaymentDataFactory' => $this->objectManager->create(CMPaymentDataFactory::class),
                'cmPaymentFactory' => $this->objectManager->create(CMPaymentFactory::class),
                'cmPaymentUrlFactory' => $this->objectManager->create(CMPaymentUrlFactory::class),
                'cmPaymentRepository' => $this->objectManager->create(CMPaymentRepositoryInterface::class),
                'cmOrderRepository' => $this->objectManager->create(CMOrderRepositoryInterface::class)
            ]
        );
    }
}

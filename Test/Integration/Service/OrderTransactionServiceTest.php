<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Api\Model\Data\PaymentInterfaceFactory;
use CM\Payments\Api\Model\PaymentRepositoryInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Api\Service\OrderTransactionServiceInterface;
use CM\Payments\Model\Data\Order;
use CM\Payments\Model\OrderRepository;
use CM\Payments\Service\OrderTransactionService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use CM\Payments\Test\Mock\MockApiResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;

class OrderTransactionServiceTest extends IntegrationTestCase
{
    /**
     * @var ApiClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var OrderTransactionServiceInterface
     */
    private $orderTransactionService;

    /**
     * @var MockApiResponse
     */
    private $mockApiResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ApiClientInterface::class);

        $this->mockApiResponse = $this->objectManager->create(MockApiResponse::class);
        $orderClient = $this->objectManager->create(\CM\Payments\Client\Order::class, [
            'apiClient' => $this->clientMock,
        ]);

        $this->orderTransactionService = $this->objectManager->create(OrderTransactionService::class, [
            'orderClient' => $orderClient
        ]);

        $magentoOrder = $this->loadOrderById('100000001');

        $this->createCmOrder($magentoOrder);

        $magentoOrder = $this->loadOrderById('100000001');
        $this->adjustMagentoOrder($magentoOrder);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testProcess()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetail());
        $this->orderTransactionService->process('100000001');
        $magentoOrder = $this->loadOrderById('100000001');

        $this->assertSame(\Magento\Sales\Model\Order::STATE_PROCESSING, $magentoOrder->getStatus());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testProcessWhenOrderKeyNotExists()
    {
        $this->clientMock
            ->expects($this->never())
            ->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetail());
        $this->expectException(NoSuchEntityException::class);
        $this->orderTransactionService->process('bla');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testProcessWhenCMOrderNotFound()
    {
        $this->clientMock->expects($this->once())->method('execute')->willThrowException(
            new RequestException(
                json_encode(['messages' => 'Order could not be found with the given key.']),
                new Request('GET', 'test'),
                new Response(400)
            )
        );
        $this->expectException(NoSuchEntityException::class);
        $this->orderTransactionService->process('100000001');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testProcessWhenOrderNotConsideredSafe()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetailConsideredFast());
        $this->orderTransactionService->process('100000001');
        $magentoOrder = $this->loadOrderById('100000001');

        $this->assertSame(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, $magentoOrder->getStatus());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreatedInvoiceAfterProcess()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetail());
        $this->orderTransactionService->process('100000001');
        $magentoOrder = $this->loadOrderById('100000001');

        $this->assertSame('100.0000', $magentoOrder->getTotalInvoiced());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreatedTransactionAfterProcess()
    {
        $this->clientMock
            ->expects($this->once())->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetail());
        $this->orderTransactionService->process('100000001');
        $magentoOrder = $this->loadOrderById('100000001');

        $this->assertSame('pid4911203603t', $magentoOrder->getPayment()->getLastTransId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShouldNotProcessWhenOrderIsClosed()
    {
        $this->clientMock
            ->expects($this->never())->method('execute');

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder->setState(\Magento\Sales\Model\Order::STATE_CLOSED);
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        $this->orderTransactionService->process('100000001');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShouldNotProcessWhenOrderAlreadyProcessed()
    {
        $this->clientMock
            ->expects($this->once())->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetail());

        // first time
        $this->orderTransactionService->process('100000001');
        // second time
        $this->orderTransactionService->process('100000001');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testMultiplePayments()
    {
        $this->clientMock
            ->expects($this->once())->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetailMultiplePayments());
        $this->orderTransactionService->process('100000001');
        $magentoOrder = $this->loadOrderById('100000001');

        $this->assertSame('pid4911203603t', $magentoOrder->getPayment()->getLastTransId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testIfCMPaymentIsCreatedInDatabase()
    {
        $this->clientMock
            ->expects($this->once())->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetail());

        $this->orderTransactionService->process('100000001');

        $cmPaymentRepository = $this->objectManager->create(PaymentRepositoryInterface::class);
        $cmPayment = $cmPaymentRepository->getByOrderKey('test123');
        $this->assertSame('pid4911203603t', $cmPayment->getPaymentId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShouldCancelOrderWhenCreditCardDirectPaymentIsCanceled()
    {
        $this->clientMock
            ->expects($this->once())->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetailCanceledPayment());

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder->getPayment()->setMethod('cm_payments_creditcard');
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        $this->createCmOrder($magentoOrder);

        $this->createCMPayment($magentoOrder);

        $this->orderTransactionService->process('100000001');

        $magentoOrder = $this->loadOrderById('100000001');
        $this->assertSame('canceled', $magentoOrder->getStatus());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShouldNotCancelOrderWhenCreditCardDirectPaymentIsRedirectedForAuthentication()
    {
        $this->clientMock
            ->expects($this->once())->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetailRedirectedForAuthenticationPayment());

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder->getPayment()->setMethod('cm_payments_creditcard');
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        $this->createCmOrder($magentoOrder);
        $this->createCMPayment($magentoOrder);

        $this->orderTransactionService->process('100000001');

        $magentoOrder = $this->loadOrderById('100000001');
        $this->assertSame('pending_payment', $magentoOrder->getStatus());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShouldNotCancelOrderWhenIdealPaymentIsCanceled()
    {
        $this->clientMock
            ->expects($this->once())->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetailCanceledPayment());

        $magentoOrder = $this->loadOrderById('100000001');

        $magentoOrder->getPayment()->setMethod('cm_payments_ideal');
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        $this->createCmOrder($magentoOrder);
        $this->orderTransactionService->process('100000001');

        $magentoOrder = $this->loadOrderById('100000001');
        $this->assertSame('pending_payment', $magentoOrder->getStatus());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShouldNotCancelOrderWhenCMPaymentNotExists()
    {
        $this->clientMock
            ->expects($this->once())->method('execute')
            ->willReturn($this->mockApiResponse->getOrderDetailCanceledPayment());

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder->getPayment()->setMethod('cm_payments_ideal');
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        $this->createCmOrder($magentoOrder);
        $this->orderTransactionService->process('100000001');

        $magentoOrder = $this->loadOrderById('100000001');
        $this->assertSame('pending_payment', $magentoOrder->getStatus());
    }

    /**
     * @param OrderInterface $magentoOrder
     * @return OrderInterface
     */
    private function adjustMagentoOrder(OrderInterface $magentoOrder): OrderInterface
    {
        /** @var OrderInterface $magentoOrder */
        $magentoOrder
            ->setOrderCurrencyCode('USD')
            ->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
            ->setBaseCurrencyCode('USD');

        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        return $magentoOrder;
    }

    /**
     * @param OrderInterface $magentoOrder
     */
    private function createCmOrder(OrderInterface $magentoOrder): void
    {
        $cmOrder = $this->objectManager->create(Order::class);
        $cmOrder->setIncrementId('100000001')
            ->setOrderId($magentoOrder->getEntityId())
            ->setOrderKey('test123');

        $cmOrderRepository = $this->objectManager->create(OrderRepository::class);
        $cmOrderRepository->save($cmOrder);
    }

    /**
     * @param OrderInterface $magentoOrder
     */
    private function createCMPayment(OrderInterface $magentoOrder): void
    {
        $cmPaymentRepository = $this->objectManager->create(PaymentRepositoryInterface::class);
        $cmPaymentDataFactory = $this->objectManager->create(PaymentInterfaceFactory::class);
        $cmPayment = $cmPaymentDataFactory->create();
        $cmPayment->setOrderId((int)$magentoOrder->getEntityId());
        $cmPayment->setOrderKey('test123');
        $cmPayment->setIncrementId($magentoOrder->getIncrementId());
        $cmPayment->setPaymentId('pid4911203603t');
        $cmPaymentRepository->save($cmPayment);
    }
}

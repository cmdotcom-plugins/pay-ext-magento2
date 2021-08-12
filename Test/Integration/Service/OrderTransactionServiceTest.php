<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Api\Service\OrderTransactionServiceInterface;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\Data\Order;
use CM\Payments\Service\OrderTransactionService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use CM\Payments\Test\Mock\MockApiResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
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

        $cmOrder = $this->objectManager->create(Order::class);
        $cmOrder->setIncrementId('100000001')
            ->setOrderId($magentoOrder->getEntityId())
            ->setOrderKey('test123');

        $cmOrderRepository = $this->objectManager->create(\CM\Payments\Model\OrderRepository::class);
        $cmOrderRepository->save($cmOrder);

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
}

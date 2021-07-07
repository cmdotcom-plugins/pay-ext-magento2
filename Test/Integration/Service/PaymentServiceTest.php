<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Api\Model\Data\OrderInterfaceFactory;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
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
    public function testCreateIdealPayment()
    {
        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $cmOrderFactory = $this->objectManager->create(OrderInterfaceFactory::class);
        $cmOrderOrder = $cmOrderFactory->create();
        $cmOrderRepository = $this->objectManager->get(CMOrderRepositoryInterface::class);
        $cmOrderOrder->setIncrementId($magentoOrder->getIncrementId());
        $cmOrderOrder->setOrderId((int)$magentoOrder->getEntityId());
        $cmOrderOrder->setOrderKey('0287A1617D93780EF28044B98438BF2F');
        $cmOrderRepository->save($cmOrderOrder);

        $this->clientMock->expects($this->once())->method('execute')->willReturn(
            [
                'id' => 'pid4911257676t',
                'status' => 'REDIRECTED_FOR_AUTHORIZATION',
                'urls' => [
                    0 => [
                        'purpose' => 'REDIRECT',
                        'method' => 'GET',
                        //phpcs:ignore
                        'url' => 'https://test.docdatapayments.com/ps_sim/idealbanksimulator.jsf?trxid=1625579689224&ec=4911257676&returnUrl=https%3A%2F%2Ftestsecure.docdatapayments.com%2Fps%2FreturnFromAuthorization%3FpaymentReference%3D49112576765AD00EC846B52EAED61E9FC2530CFF90%26checkDigitId%3D49112576765AD00EC846B52EAED61E9FC2530CFF90',
                        'order' => 1,
                    ],
                ]
            ]
        );

        $payment = $this->paymentService->create($magentoOrder->getId());
        $this->assertNotNull(
            $payment->getId()
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
            ->setOrderCurrencyCode('EUR')
            ->setBaseCurrencyCode('EUR');

        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        return $magentoOrder;
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSavePaymentInDatabase()
    {
        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $cmOrderFactory = $this->objectManager->create(OrderInterfaceFactory::class);
        $cmOrderOrder = $cmOrderFactory->create();
        $cmOrderRepository = $this->objectManager->get(CMOrderRepositoryInterface::class);
        $cmOrderOrder->setIncrementId($magentoOrder->getIncrementId());
        $cmOrderOrder->setOrderId((int)$magentoOrder->getEntityId());
        $cmOrderOrder->setOrderKey('2287A1617D93780EF28044B98438BF2G');
        $cmOrderRepository->save($cmOrderOrder);

        $this->clientMock->expects($this->once())->method('execute')->willReturn(
            [
                'id' => 'pid4911257677t',
                'status' => 'REDIRECTED_FOR_AUTHORIZATION',
                'urls' => [
                    0 => [
                        'purpose' => 'REDIRECT',
                        'method' => 'GET',
                        //phpcs:ignore
                        'url' => 'https://test.docdatapayments.com/ps_sim/idealbanksimulator.jsf?trxid=1625579689224&ec=4911257676&returnUrl=https%3A%2F%2Ftestsecure.docdatapayments.com%2Fps%2FreturnFromAuthorization%3FpaymentReference%3D49112576765AD00EC846B52EAED61E9FC2530CFF90%26checkDigitId%3D49112576765AD00EC846B52EAED61E9FC2530CFF90',
                        'order' => 1,
                    ],
                ]
            ]
        );

        $this->paymentService->create($magentoOrder->getId());

        /** @var CMPaymentRepositoryInterface $cmOrderRepository */
        $cmPaymentRepository = $this->objectManager->create(CMPaymentRepositoryInterface::class);

        $resultByOrderKey = $cmPaymentRepository->getByOrderKey('2287A1617D93780EF28044B98438BF2G');
        $this->assertSame((int)$magentoOrder->getId(), $resultByOrderKey->getOrderId());

        $resultByPaymentId = $cmPaymentRepository->getByPaymentId('pid4911257677t');
        $this->assertSame((int)$magentoOrder->getId(), $resultByPaymentId->getOrderId());
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

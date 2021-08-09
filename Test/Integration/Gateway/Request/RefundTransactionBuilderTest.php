<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Gateway\Request;

use CM\Payments\Client\Model\Request\RefundCreate;
use CM\Payments\Gateway\Request\Builder\RefundTransactionBuilder;
use CM\Payments\Model\Data\Order;
use CM\Payments\Model\Data\Payment;
use CM\Payments\Model\OrderRepository;
use CM\Payments\Model\PaymentRepository;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\CouldNotRefundException;
use Magento\Sales\Model\Order\CreditmemoRepository;

class RefundTransactionBuilderTest extends IntegrationTestCase
{
    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/creditmemo_for_get.php
     */
    public function testBuildRefundTransaction()
    {
        /** @var RefundTransactionBuilder $refundTransactionBuilder */
        $refundTransactionBuilder = $this->objectManager->create(RefundTransactionBuilder::class);

        $stateObject = new DataObject();
        $order = $this->loadOrderById('100000001');
        $creditmemo = $this->getCreditMemo('100000001');

        $order->getPayment()->setCreditMemo($creditmemo);
        $paymentDataObject = $this->getNewPaymentDataObject($order);

        $buildSubject = [
            'stateObject' => $stateObject,
            'payment' => $paymentDataObject,
            'amount' => $order->getGrandTotal(),
            'currency' => $order->getOrderCurrencyCode()
        ];
        $this->createCmOrder($order);
        $this->createCmPayment($order);

        $actual = $refundTransactionBuilder->build($buildSubject);

        $this->assertArrayHasKey('payload', $actual);
        $this->assertInstanceOf(RefundCreate::class, $actual['payload']);
        $this->assertEquals(10000, $actual['payload']->getAmount());
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testNoAmount()
    {
        /** @var RefundTransactionBuilder $refundTransactionBuilder */
        $refundTransactionBuilder = $this->objectManager->create(RefundTransactionBuilder::class);

        $stateObject = new DataObject();
        $order = $this->loadOrderById('100000001');
        $paymentDataObject = $this->getNewPaymentDataObject($order);

        $buildSubject = [
            'stateObject' => $stateObject,
            'payment' => $paymentDataObject,
            'amount' => 0,
            'currency' => $order->getOrderCurrencyCode()
        ];

        $this->expectException(CouldNotRefundException::class);
        $refundTransactionBuilder->build($buildSubject);
    }

    /**
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
     * @param $orderId
     * @return OrderInterface
     */
    private function getCreditMemo($orderId)
    {
        $repository = $this->objectManager->get(CreditmemoRepository::class);
        $builder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $builder->addFilter('increment_id', $orderId, 'eq')->create();

        $list = $repository->getList($searchCriteria)->getItems();

        return array_shift($list);
    }

    /**
     * @param OrderInterface $order
     * @return PaymentDataObjectInterface
     */
    private function getNewPaymentDataObject(OrderInterface $order): PaymentDataObjectInterface
    {
        /** @var PaymentDataObjectFactoryInterface $paymentDataObjectFactory */
        $paymentDataObjectFactory = $this->objectManager->get(PaymentDataObjectFactoryInterface::class);
        return $paymentDataObjectFactory->create($order->getPayment());
    }

    /**
     * @param OrderInterface $order
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function createCmOrder(OrderInterface $order): void
    {
        /** @var OrderRepository $cmOrderRepository */
        $cmOrderRepository = $this->objectManager->create(OrderRepository::class);

        /** @var Order $cmOrder */
        $cmOrder = $this->objectManager->create(Order::class);
        $cmOrder->setData([
            'order_id' => (int)$order->getEntityId(),
            'increment_id' => $order->getIncrementId(),
            'order_key' => '123',
        ]);
        $cmOrderRepository->save($cmOrder);
    }

    /**
     * @param OrderInterface $order
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function createCmPayment(OrderInterface $order): void
    {
        /** @var PaymentRepository $cmPaymentRepository */
        $cmPaymentRepository = $this->objectManager->create(PaymentRepository::class);
        /** @var Payment $cmPayment */
        $cmPayment = $this->objectManager->create(Payment::class);
        $cmPayment->setData([
            'order_id' => (int)$order->getEntityId(),
            'order_key' => '123',
            'increment_id' => $order->getIncrementId(),
            'payment_id' => 'p123'
        ]);
        $cmPaymentRepository->save($cmPayment);
    }
}

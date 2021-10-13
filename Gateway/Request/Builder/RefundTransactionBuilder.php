<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Gateway\Request\Builder;

use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Client\Model\Request\RefundCreate;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Exception\CouldNotRefundException;

class RefundTransactionBuilder implements BuilderInterface
{
    /**
     * @var State
     */
    private $state;
    /**
     * @var CMOrderRepositoryInterface
     */
    private $cmOrderRepository;
    /**
     * @var PaymentRepositoryInterface
     */
    private $cmPaymentRepository;

    /**
     * RefundTransactionBuilder constructor.
     *
     * @param State $state
     */
    public function __construct(
        State $state,
        CMOrderRepositoryInterface $cmOrderRepository,
        PaymentRepositoryInterface $cmPaymentRepository
    ) {
        $this->state = $state;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->cmPaymentRepository = $cmPaymentRepository;
    }

    /**
     * @param array $buildSubject
     * @return RefundCreate[]
     * @throws CouldNotRefundException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(array $buildSubject): array
    {
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $amount = (float)SubjectReader::readAmount($buildSubject);

        if ($amount <= 0) {
            throw new CouldNotRefundException(
                __('Refunds with 0 amount can not be processed. Please set a different amount')
            );
        }

        $payment = $paymentDataObject->getPayment();

        /**
         * @var \Magento\Sales\Model\Order\Creditmemo $creditMemo
         */
        $creditMemo = $payment->getCreditMemo();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        $orderId = $order->getIncrementId();

        $cmOrder = $this->cmOrderRepository->getByOrderId((int) $order->getEntityId());
        $cmPayment = $this->cmPaymentRepository->getByOrderKey($cmOrder->getOrderKey());

        if (! in_array($cmPayment->getPaymentMethod(), MethodServiceInterface::ALLOW_REFUND_METHODS)) {
            throw new CouldNotRefundException(
                __('Payment method %1 doesn\'t support refund', $cmPayment->getPaymentMethod())
            );
        }

        $payload = new RefundCreate(
            $cmOrder->getOrderKey(),
            $cmPayment->getPaymentId(),
            $orderId,
            'Refund of Magento Order ' .$orderId,
            (int) (($amount * 100) + ($creditMemo->getAdjustment() * 100)),
            $order->getOrderCurrencyCode()
        );

        return [
            'payload' => $payload
        ];
    }
}

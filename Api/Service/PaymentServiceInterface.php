<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use CM\Payments\Api\Model\Domain\PaymentOrderStatusInterface;
use CM\Payments\Client\Api\CMPaymentInterface;
use CM\Payments\Exception\EmptyPaymentIdException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

interface PaymentServiceInterface
{
    /**
     * @param int $orderId
     * @param CardDetailsInterface|null $cardDetails
     * @param BrowserDetailsInterface|null $browserDetails
     * @return \CM\Payments\Client\Api\CMPaymentInterface
     * @throws NoSuchEntityException
     * @throws EmptyPaymentIdException
     */
    public function create(
        int $orderId,
        CardDetailsInterface $cardDetails = null,
        BrowserDetailsInterface $browserDetails = null
    ): CMPaymentInterface;

    /**
     * @param string $paymentId
     * @return \CM\Payments\Api\Model\Domain\PaymentOrderStatusInterface
     */
    public function getPaymentStatus(string $paymentId): PaymentOrderStatusInterface;

    /**
     * Manually capture Klarna payment
     * @param Order $order
     * @return void
     */
    public function captureKlarnaPayment(Order $order): void;
}

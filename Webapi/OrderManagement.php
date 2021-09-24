<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Webapi;

use CM\Payments\Api\OrderManagementInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Api\CMPaymentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderManagement implements OrderManagementInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    /**
     * OrderManagement constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderServiceInterface $orderService
     * @param PaymentServiceInterface $paymentService
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderServiceInterface $orderService,
        PaymentServiceInterface $paymentService
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * {@inheritDoc}
     */
    public function processOrder(
        int $orderId
    ): CMPaymentInterface {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->get($orderId);

        if (!$order) {
            throw new NoSuchEntityException(__('Order not found.'));
        }

        $this->orderService->create($order->getId());

        $cmPayment = $this->paymentService->create($order->getId());
        if (!$cmPayment->getRedirectUrl()) {
            throw new LocalizedException(__('No redirect url found in payment response.'));
        }

        return $cmPayment;
    }
}

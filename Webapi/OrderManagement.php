<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Webapi;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\OrderManagementInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Api\CMPaymentInterface;
use CM\Payments\Client\Model\CMPayment;
use CM\Payments\Exception\PaymentMethodNotFoundException;
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
     * @var MethodServiceInterface
     */
    private $methodService;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * OrderManagement constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderServiceInterface $orderService
     * @param PaymentServiceInterface $paymentService
     * @param MethodServiceInterface $methodService
     * @param ConfigInterface $config
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderServiceInterface $orderService,
        PaymentServiceInterface $paymentService,
        MethodServiceInterface $methodService,
        ConfigInterface $config
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->methodService = $methodService;
        $this->config = $config;
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

        if ($this->methodService->isCmPaymentsMethod($order->getPayment()->getMethod()) === false) {
            throw new PaymentMethodNotFoundException(__('Order Payment method is not a CM.com payment method.'));
        }

        $cmOrder = $this->orderService->create((int) $order->getId());
        if ($this->config->isMethodDirect($order->getPayment()->getMethod())) {
            $cmPayment = $this->paymentService->create((int) $order->getId());
            if (!$cmPayment->getRedirectUrl()) {
                throw new LocalizedException(__('No redirect url found in payment response.'));
            }

            return $cmPayment;
        }

        return new CMPayment(
            $cmOrder->getOrderKey(),
            'REDIRECTED_FOR_AUTHORIZATION',
            $cmOrder->getUrl(),
            []
        );
    }
}

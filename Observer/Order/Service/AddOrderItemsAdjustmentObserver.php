<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Observer\Order\Service;

use CM\Payments\Api\Service\Order\Item\AddOrderItemsAdjustmentServiceInterface;
use CM\Payments\Client\Api\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * We need this observer due to rounding problems of Magento
 * See: \CM\Payments\Service\Order\Item\AddOrderItemsAdjustmentService
 */
class AddOrderItemsAdjustmentObserver implements ObserverInterface
{
    /**
     * @var AddOrderItemsAdjustmentServiceInterface
     */
    private $addOrderItemsAdjustmentService;

    /**
     * AddOrderItemsAdjustmentObserver constructor.
     * @param AddOrderItemsAdjustmentServiceInterface $addOrderItemsAdjustmentService
     */
    public function __construct(
        AddOrderItemsAdjustmentServiceInterface $addOrderItemsAdjustmentService
    ) {
        $this->addOrderItemsAdjustmentService = $addOrderItemsAdjustmentService;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getDataByKey('order');
        $grandTotal = (int)round($order->getGrandTotal() * 100);

        /** @var RequestInterface $orderItemsCreateRequest */
        $orderItemsCreateRequest = $observer->getEvent()->getDataByKey('orderItemsCreateRequest');

        $this->addOrderItemsAdjustmentService->execute(
            $grandTotal,
            $order->getOrderCurrencyCode(),
            $orderItemsCreateRequest
        );
    }
}

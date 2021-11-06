<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Observer\Method\Service;

use CM\Payments\Api\Service\Order\Item\AddOrderItemsAdjustmentServiceInterface;
use CM\Payments\Client\Api\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * We need this observer due to rounding problems of Magento, see \CM\Payments\Service\Order\Item\AddOrderItemsAdjustmentService
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
        /** @var CartInterface $quote */
        $quote = $observer->getEvent()->getDataByKey('quote');
        $grandTotal = (int)round($quote->getGrandTotal() * 100);

        /** @var RequestInterface $orderItemsCreateRequest */
        $orderItemsCreateRequest = $observer->getEvent()->getDataByKey('orderItemsCreateRequest');

        $this->addOrderItemsAdjustmentService->execute(
            $grandTotal,
            $quote->getQuoteCurrencyCode(),
            $orderItemsCreateRequest
        );
    }
}

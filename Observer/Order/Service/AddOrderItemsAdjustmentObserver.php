<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Observer\Order\Service;

use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use CM\Payments\Client\Model\Request\OrderItemCreateFactory as ClientOrderItemCreateFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;

class AddOrderItemsAdjustmentObserver implements ObserverInterface
{
    /**
     * @var ClientOrderItemCreateFactory
     */
    private $clientOrderItemCreateFactory;

    /**
     * AddOrderItemsAdjustmentObserver constructor
     *
     * @param ClientOrderItemCreateFactory $clientOrderItemCreateFactory
     */
    public function __construct(
        ClientOrderItemCreateFactory $clientOrderItemCreateFactory
    ) {
        $this->clientOrderItemCreateFactory = $clientOrderItemCreateFactory;
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

        /** @var RequestInterface $orderCreateItemsRequest */
        $orderCreateItemsRequest = $observer->getEvent()->getDataByKey('orderCreateItemsRequest');

        $itemsTotal = 0;
        foreach ($orderCreateItemsRequest->getPayload() as $item) {
            $itemsTotal += $item['amount'];
        }

        $max = $itemsTotal + 0.05 * 100;
        $min = $itemsTotal - 0.05 * 100;
        if (($min <= $grandTotal) && ($grandTotal <= $max)) {
            $difference = $grandTotal - $itemsTotal;

            /** @var OrderItemCreate $orderCreate */
            $orderItemCreate = $this->clientOrderItemCreateFactory->create();

            $orderItemCreate->setItemId(count($orderCreateItemsRequest->getPayload()) + 1);
            $orderItemCreate->setType(OrderItemsRequestBuilderInterface::TYPE_DISCOUNT);
            $orderItemCreate->setSku(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_SKU);
            $orderItemCreate->setName(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_NAME);
            $orderItemCreate->setDescription(OrderItemsRequestBuilderInterface::ITEM_ADJUSTMENT_FEE_NAME);
            $orderItemCreate->setQuantity(1);
            $orderItemCreate->setUnitAmount((int)round($difference));
            $orderItemCreate->setAmount((int)round($difference));
            $orderItemCreate->setCurrency($order->getOrderCurrencyCode());
            $orderItemCreate->setVatRate(sprintf("%.1f", 0));
            $orderItemCreate->setVatAmount(0);

            $orderCreateItemsRequest->addOrderItem($orderItemCreate);
        }
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\Order\Item\Request\RequestPartByOrderItemInterface;
use CM\Payments\Api\Service\Order\Item\Request\RequestPartByQuoteItemInterface;
use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use CM\Payments\Client\Model\Request\OrderItemCreateFactory as ClientOrderItemCreateFactory;
use CM\Payments\Client\Request\OrderItemsCreateRequest;
use CM\Payments\Client\Request\OrderItemsCreateRequestFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class OrderItemsRequestBuilder implements OrderItemsRequestBuilderInterface
{
    /**
     * @var ClientOrderItemCreateFactory
     */
    private $clientOrderItemCreateFactory;

    /**
     * @var OrderItemsCreateRequestFactory
     */
    private $orderItemsCreateRequestFactory;

    /**
     * @var RequestPartByOrderItemInterface[]
     */
    private $orderItemRequestParts;

    /**
     * @var RequestPartByQuoteItemInterface[]
     */
    private $quoteItemRequestParts;

    /**
     * OrderItemsRequestBuilder constructor
     *
     * @param ClientOrderItemCreateFactory $clientOrderItemCreateFactory
     * @param OrderItemsCreateRequestFactory $orderItemsCreateRequestFactory
     * @param RequestPartByOrderItemInterface[] $orderItemRequestParts
     * @param RequestPartByQuoteItemInterface[] $quoteItemRequestParts
     */
    public function __construct(
        ClientOrderItemCreateFactory $clientOrderItemCreateFactory,
        OrderItemsCreateRequestFactory $orderItemsCreateRequestFactory,
        array $orderItemRequestParts,
        array $quoteItemRequestParts
    ) {
        $this->clientOrderItemCreateFactory = $clientOrderItemCreateFactory;
        $this->orderItemsCreateRequestFactory = $orderItemsCreateRequestFactory;
        $this->orderItemRequestParts = $orderItemRequestParts;
        $this->quoteItemRequestParts = $quoteItemRequestParts;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderKey, array $orderItems): OrderItemsCreateRequest
    {
        $orderItemsCreate = [];
        $orderItems = $this->addDynamicOrderItems($orderItems);
        foreach ($orderItems as $orderItem) {
            /** @var OrderItemCreate $orderItemCreate */
            $orderItemCreate = $this->clientOrderItemCreateFactory->create();

            foreach ($this->orderItemRequestParts as $part) {
                $orderItemCreate = $part->process($orderItem, $orderItemCreate);
            }

            $orderItemsCreate[] = $orderItemCreate;
        }

        return $this->orderItemsCreateRequestFactory->create([
                                                                 'orderKey' => $orderKey,
                                                                 'orderItems' => $orderItemsCreate
                                                             ]);
    }

    /**
     * @param array $orderItems
     * @return array
     */
    private function addDynamicOrderItems(array $orderItems): array
    {
        $lastItem = array_slice($orderItems, -1);

        if (!empty($lastItem[0])) {
            $order = $lastItem[0]->getOrder();

            // Adding of shipping fee item
            if ((float)$order->getShippingAmount()) {
                /** @var OrderItemInterface $shippingItem */
                $shippingItem = clone $lastItem[0];
                $shippingItem->setOrder($order);
                $shippingItem->setIsVirtual(0);
                $shippingItem->setItemId($lastItem[0]->getItemId() + 1);
                $shippingItem->setSku(self::ITEM_SHIPPING_FEE_SKU);
                $shippingItem->setName(self::ITEM_SHIPPING_FEE_NAME);
                $shippingItem->setDescription(self::ITEM_SHIPPING_FEE_NAME);
                $shippingItem->setQtyOrdered(1);

                $orderItems[] = $shippingItem;
            }
        }

        return $orderItems;
    }

    /**
     * @inheritDoc
     */
    public function createByQuoteItems(string $orderKey, array $quoteItems): OrderItemsCreateRequest
    {
        $orderItemsCreate = [];
        $quoteItems = $this->addDynamicQuoteItems($quoteItems);
        foreach ($quoteItems as $quoteItem) {
            /** @var OrderItemCreate $orderItemCreate */
            $orderItemCreate = $this->clientOrderItemCreateFactory->create();

            foreach ($this->quoteItemRequestParts as $part) {
                $orderItemCreate = $part->process($quoteItem, $orderItemCreate);
            }

            $orderItemsCreate[] = $orderItemCreate;
        }

        return $this->orderItemsCreateRequestFactory->create([
                                                                 'orderKey' => $orderKey,
                                                                 'orderItems' => $orderItemsCreate
                                                             ]);
    }

    /**
     * @param array $quoteItems
     * @return array
     */
    private function addDynamicQuoteItems(array $quoteItems): array
    {
        $lastItem = array_slice($quoteItems, -1);

        if (!empty($lastItem[0])) {
            $quote = $lastItem[0]->getQuote();

            // Adding of shipping fee item
            if ((float)$quote->getShippingAddress()->getShippingAmount()) {
                /** @var CartItemInterface $shippingItem */
                $shippingItem = clone $lastItem[0];
                $shippingItem->setQuote($quote);
                $shippingItem->setIsVirtual(0);
                $shippingItem->setItemId($lastItem[0]->getItemId() + 1);
                $shippingItem->setSku(self::ITEM_SHIPPING_FEE_SKU);
                $shippingItem->setName(self::ITEM_SHIPPING_FEE_NAME);
                $shippingItem->setDescription(self::ITEM_SHIPPING_FEE_NAME);
                $shippingItem->setQty(1);

                $quoteItems[] = $shippingItem;
            }
        }

        return $quoteItems;
    }
}

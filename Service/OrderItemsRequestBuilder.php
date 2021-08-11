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
            /** @var OrderItemCreate $orderCreate */
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
     * @inheritDoc
     */
    public function createByQuoteItems(string $orderKey, array $quoteItems): OrderItemsCreateRequest
    {
        $orderItemsCreate = [];
        $quoteItems = $this->addDynamicQuoteItems($quoteItems);
        foreach ($quoteItems as $quoteItem) {
            /** @var OrderItemCreate $orderCreate */
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
                $shippingItem->setDescription('');
                $shippingItem->setQtyOrdered(1);
                $shippingItem->setTaxAmount(0);
                $shippingItem->setPriceInclTax($order->getShippingAmount());
                $shippingItem->setRowTotalInclTax($order->getShippingAmount());

                $orderItems[] = $shippingItem;
            }

            // Adding of discount item
            if ((float)$order->getDiscountAmount()) {
                /** @var OrderItemInterface $discountItem */
                $discountItem = clone $lastItem[0];
                $discountItem->setOrder($order);
                $discountItem->setIsVirtual(0);
                $discountItem->setItemId->setItemId(
                    isset($shippingItem) ? $shippingItem->getItemId() + 1 : $lastItem[0]->getItemId() + 1
                );
                $discountItem->setSku(self::ITEM_DISCOUNT_SKU);
                $discountItem->setName(self::ITEM_DISCOUNT_NAME);
                $discountItem->setDescription('');
                $discountItem->setQtyOrdered(1);
                $discountItem->setTaxAmount(0);
                $discountItem->setPriceInclTax(-$order->getDiscountAmount());
                $discountItem->setRowTotalInclTax(-$order->getDiscountAmount());

                $orderItems[] = $discountItem;
            }
        }

        return $orderItems;
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
                $shippingItem->setDescription('');
                $shippingItem->setQty(1);
                $shippingItem->setTaxAmount(0);
                $shippingItem->setPriceInclTax($quote->getShippingAddress()->getShippingAmount());
                $shippingItem->setRowTotalInclTax($quote->getShippingAddress()->getShippingAmount());

                $quoteItems[] = $shippingItem;
            }

            // Adding of discount item
            if ((float)$quote->getShippingAddress()->getDiscountAmount()) {
                /** @var CartItemInterface $discountItem */
                $discountItem = clone $lastItem[0];
                $discountItem->setQuote($quote);
                $discountItem->setIsVirtual(0);
                $discountItem->setItemId(
                    isset($shippingItem) ? $shippingItem->getItemId() + 1 : $lastItem[0]->getItemId() + 1
                );
                $discountItem->setSku(self::ITEM_DISCOUNT_SKU);
                $discountItem->setName(self::ITEM_DISCOUNT_NAME);
                $discountItem->setDescription('');
                $discountItem->setQty(1);
                $discountItem->setTaxAmount(0);
                $discountItem->setPriceInclTax(-$quote->getShippingAddress()->getDiscountAmount());
                $discountItem->setRowTotalInclTax(-$quote->getShippingAddress()->getDiscountAmount());

                $quoteItems[] = $discountItem;
            }
        }

        return $quoteItems;
    }
}

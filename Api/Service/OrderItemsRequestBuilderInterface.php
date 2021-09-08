<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Request\OrderItemsCreateRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

interface OrderItemsRequestBuilderInterface
{
    /**
     * Dynamic items skus
     */
    public const ITEM_SHIPPING_FEE_SKU = 'cm-shipping-fee';
    public const ITEM_DISCOUNT_SKU = 'cm-discount';

    /**
     * Dynamic items names
     */
    public const ITEM_SHIPPING_FEE_NAME = 'CM Shipping Fee';
    public const ITEM_DISCOUNT_NAME = 'CM Discount';

    /**
     * Item types
     */
    public const TYPE_PHYSICAL = 'PHYSICAL';
    public const TYPE_DIGITAL = 'DIGITAL';
    public const TYPE_DISCOUNT = 'DISCOUNT';
    public const TYPE_GIFT_CARD = 'GIFT_CARD';
    public const TYPE_STORE_CREDIT = 'STORE_CREDIT';
    public const TYPE_SHIPPING_FEE = 'SHIPPING_FEE';
    public const TYPE_SALES_TAX = 'SALES_TAX';
    public const TYPE_SURCHARGE = 'SURCHARGE';

    /**
     * @param string $orderKey
     * @param OrderItemInterface[] $orderItems
     * @return OrderItemsCreateRequest
     * @throws LocalizedException
     */
    public function create(string $orderKey, array $orderItems): OrderItemsCreateRequest;

    /**
     * @param string $orderKey
     * @param CartItemInterface[] $quoteItems
     * @return OrderItemsCreateRequest
     * @throws LocalizedException
     */
    public function createByQuoteItems(string $orderKey, array $quoteItems): OrderItemsCreateRequest;
}

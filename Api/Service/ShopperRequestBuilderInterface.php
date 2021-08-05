<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Request\ShopperCreateRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

interface ShopperRequestBuilderInterface
{
    /**
     * @param OrderAddressInterface $orderAddress
     * @return ShopperCreateRequest
     * @throws LocalizedException
     */
    public function createByOrderAddress(OrderAddressInterface $orderAddress): ShopperCreateRequest;

    /**
     * @param AddressInterface $quoteAddress
     * @return ShopperCreateRequest
     * @throws LocalizedException
     */
    public function createByQuoteAddress(AddressInterface $quoteAddress): ShopperCreateRequest;
}

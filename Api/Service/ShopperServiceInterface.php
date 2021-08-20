<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Model\Response\ShopperCreate;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Framework\Exception\LocalizedException;

interface ShopperServiceInterface
{
    /**
     * @param AddressInterface $quoteAddress
     * @return ShopperCreate
     * @throws LocalizedException
     */
    public function createByQuoteAddress(
        AddressInterface $quoteAddress
    ): ShopperCreate;

    /**
     * @param OrderAddressInterface $orderAddress
     * @return ShopperCreate
     * @throws LocalizedException
     */
    public function createByOrderAddress(
        OrderAddressInterface $orderAddress
    ): ShopperCreate;
}

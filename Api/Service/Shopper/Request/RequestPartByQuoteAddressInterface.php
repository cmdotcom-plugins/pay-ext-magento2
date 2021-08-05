<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service\Shopper\Request;

use CM\Payments\Client\Model\Request\ShopperCreate;
use Magento\Quote\Api\Data\AddressInterface;

interface RequestPartByQuoteAddressInterface
{
    /**
     * @param AddressInterface $quoteAddress
     * @param ShopperCreate $shopperCreate
     * @return ShopperCreate
     */
    public function process(AddressInterface $quoteAddress, ShopperCreate $shopperCreate): ShopperCreate;
}

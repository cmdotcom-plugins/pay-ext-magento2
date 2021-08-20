<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service\Shopper\Request;

use CM\Payments\Client\Model\Request\ShopperCreate;
use Magento\Sales\Api\Data\OrderAddressInterface;

interface RequestPartByOrderAddressInterface
{
    /**
     * @param OrderAddressInterface $orderAddress
     * @param ShopperCreate $shopperCreate
     * @return ShopperCreate
     */
    public function process(OrderAddressInterface $orderAddress, ShopperCreate $shopperCreate): ShopperCreate;
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Address\Request\Part;

use CM\Payments\Api\Service\Shopper\Request\RequestPartByOrderAddressInterface;
use CM\Payments\Client\Model\Request\ShopperCreate;
use Magento\Sales\Api\Data\OrderAddressInterface;

class Email implements RequestPartByOrderAddressInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderAddressInterface $orderAddress, ShopperCreate $shopperCreate): ShopperCreate
    {
        $shopperCreate->setEmail($orderAddress->getEmail());

        return $shopperCreate;
    }
}

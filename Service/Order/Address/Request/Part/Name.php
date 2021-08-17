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

class Name implements RequestPartByOrderAddressInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderAddressInterface $orderAddress, ShopperCreate $shopperCreate): ShopperCreate
    {
        $nameObject = [
            'first' => $orderAddress->getFirstname(),
            'middle' => $orderAddress->getMiddlename(),
            'last' => $orderAddress->getLastname()
        ];

        $shopperCreate->setName(array_filter($nameObject));

        return $shopperCreate;
    }
}

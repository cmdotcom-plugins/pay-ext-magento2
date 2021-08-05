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

class Address implements RequestPartByOrderAddressInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderAddressInterface $orderAddress, ShopperCreate $shopperCreate): ShopperCreate
    {
        //TODO: Replace on separate interface with properties
        //TODO: Replace on proper data (based on separate service by recognizing this data from Street
        $shopperCreate->setAddress([
                                       'street' => implode('', $orderAddress->getStreet()),
                                       'housenumber' => '5',
                                       'housenumber_addition' => 'w',
                                       'postalcode' => $orderAddress->getPostcode(),
                                       'city' => $orderAddress->getCity(),
                                       'state' => $orderAddress->getRegionCode(),
                                       'country' => $orderAddress->getCountryId(),
                                   ]);

        return $shopperCreate;
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Address\Request\Part;

use CM\Payments\Api\Service\Shopper\Request\RequestPartByQuoteAddressInterface;
use CM\Payments\Client\Model\Request\ShopperCreate;
use Magento\Quote\Api\Data\AddressInterface;

class Address implements RequestPartByQuoteAddressInterface
{
    /**
     * @inheritDoc
     */
    public function process(AddressInterface $quoteAddress, ShopperCreate $shopperCreate): ShopperCreate
    {
        //TODO: Replace on separate interface with properties
        //TODO: Replace on proper data (based on separate service by recognizing this data from Street
        $shopperCreate->setAddress([
                                       'street' => implode('', $quoteAddress->getStreet()),
                                       'housenumber' => '5',
                                       'housenumber_addition' => 'w',
                                       'postalcode' => $quoteAddress->getPostcode(),
                                       'city' => $quoteAddress->getCity(),
                                       'state' => $quoteAddress->getRegionCode(),
                                       'country' => $quoteAddress->getCountryId(),
                                   ]);

        return $shopperCreate;
    }
}

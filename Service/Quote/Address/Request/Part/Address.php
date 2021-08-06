<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Address\Request\Part;

use CM\Payments\Api\Service\AddressServiceInterface;
use CM\Payments\Api\Service\Shopper\Request\RequestPartByQuoteAddressInterface;
use CM\Payments\Client\Model\Request\ShopperCreate;
use Magento\Quote\Api\Data\AddressInterface;

class Address implements RequestPartByQuoteAddressInterface
{
    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    /**
     * Address constructor
     *
     * @param AddressServiceInterface $addressService
     */
    public function __construct(
        AddressServiceInterface $addressService
    ) {
        $this->addressService = $addressService;
    }

    /**
     * @inheritDoc
     */
    public function process(AddressInterface $quoteAddress, ShopperCreate $shopperCreate): ShopperCreate
    {
        $addressObject = [
            'street' => implode('', $quoteAddress->getStreet()),
            'housenumber' => '',
            'housenumber_addition' => '',
            'postalcode' => $quoteAddress->getPostcode(),
            'city' => $quoteAddress->getCity(),
            'state' => $quoteAddress->getRegionCode(),
            'country' => $quoteAddress->getCountryId()
        ];

        $addressObject = $this->addressService->process($addressObject);
        $shopperCreate->setAddress(array_filter($addressObject));

        return $shopperCreate;
    }
}

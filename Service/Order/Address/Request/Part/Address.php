<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Address\Request\Part;

use CM\Payments\Api\Service\AddressServiceInterface;
use CM\Payments\Api\Service\Shopper\Request\RequestPartByOrderAddressInterface;
use CM\Payments\Client\Model\Request\ShopperCreate;
use Magento\Sales\Api\Data\OrderAddressInterface;

class Address implements RequestPartByOrderAddressInterface
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
    public function process(OrderAddressInterface $orderAddress, ShopperCreate $shopperCreate): ShopperCreate
    {
        $addressObject = [
            'street' => implode('', $orderAddress->getStreet()),
            'housenumber' => '',
            'housenumber_addition' => '',
            'postal_code' => $orderAddress->getPostcode(),
            'city' => $orderAddress->getCity(),
            'state' => $orderAddress->getRegionCode(),
            'country' => $orderAddress->getCountryId()
        ];

        $addressObject = $this->addressService->process($addressObject);
        $shopperCreate->setAddress(array_filter($addressObject));

        return $shopperCreate;
    }
}

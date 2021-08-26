<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response;

class ShopperCreate
{
    /**
     * @var ?string
     */
    private $shopperKey;

    /**
     * @var ?string
     */
    private $addressKey;

    /**
     * ShopperCreate constructor
     *
     * @param array $shopperCreate
     */
    public function __construct(
        array $shopperCreate
    ) {
        $this->shopperKey = $shopperCreate['shopper_key'] ?? null;
        $this->addressKey = $shopperCreate['address_key'] ?? null;
    }

    /**
     * @return ?string
     */
    public function getShopperKey(): ?string
    {
        return $this->shopperKey;
    }

    /**
     * @return ?string
     */
    public function getAddressKey(): ?string
    {
        return $this->addressKey;
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\Shopper\Request\RequestPartByOrderAddressInterface;
use CM\Payments\Api\Service\Shopper\Request\RequestPartByQuoteAddressInterface;
use CM\Payments\Api\Service\ShopperRequestBuilderInterface;
use CM\Payments\Client\Model\Request\ShopperCreate;
use CM\Payments\Client\Model\Request\ShopperCreateFactory as ClientShopperCreateFactory;
use CM\Payments\Client\Request\ShopperCreateRequest;
use CM\Payments\Client\Request\ShopperCreateRequestFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

class ShopperRequestBuilder implements ShopperRequestBuilderInterface
{
    /**
     * @var ClientShopperCreateFactory
     */
    private $clientShopperCreateFactory;

    /**
     * @var ShopperCreateRequestFactory
     */
    private $shopperCreateRequestFactory;

    /**
     * @var RequestPartByOrderAddressInterface[]
     */
    private $orderAddressRequestParts;

    /**
     * @var RequestPartByQuoteAddressInterface[]
     */
    private $quoteAddressRequestParts;

    /**
     * ShopperRequestBuilder constructor
     *
     * @param ClientShopperCreateFactory $clientShopperCreateFactory
     * @param ShopperCreateRequestFactory $shopperCreateRequestFactory
     * @param RequestPartByOrderAddressInterface[] $orderAddressRequestParts
     * @param RequestPartByQuoteAddressInterface[] $quoteAddressRequestParts
     */
    public function __construct(
        ClientShopperCreateFactory $clientShopperCreateFactory,
        ShopperCreateRequestFactory $shopperCreateRequestFactory,
        array $orderAddressRequestParts,
        array $quoteAddressRequestParts
    ) {
        $this->clientShopperCreateFactory = $clientShopperCreateFactory;
        $this->shopperCreateRequestFactory = $shopperCreateRequestFactory;
        $this->orderAddressRequestParts = $orderAddressRequestParts;
        $this->quoteAddressRequestParts = $quoteAddressRequestParts;
    }

    /**
     * @inheritDoc
     */
    public function createByOrderAddress(OrderAddressInterface $orderAddress): ShopperCreateRequest
    {
        /** @var ShopperCreate $shopperCreate */
        $shopperCreate = $this->clientShopperCreateFactory->create();

        foreach ($this->orderAddressRequestParts as $part) {
            $shopperCreate = $part->process($orderAddress, $shopperCreate);
        }

        return $this->shopperCreateRequestFactory->create(['shopperCreate' => $shopperCreate]);
    }

    /**
     * @inheritDoc
     */
    public function createByQuoteAddress(AddressInterface $quoteAddress): ShopperCreateRequest
    {
        /** @var ShopperCreate $shopperCreate */
        $shopperCreate = $this->clientShopperCreateFactory->create();

        foreach ($this->quoteAddressRequestParts as $part) {
              $shopperCreate = $part->process($quoteAddress, $shopperCreate);
        }

        return $this->shopperCreateRequestFactory->create(['shopperCreate' => $shopperCreate]);
    }
}

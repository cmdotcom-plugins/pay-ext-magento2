<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByQuoteInterface;
use CM\Payments\Api\Service\ShopperServiceInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Quote\Api\Data\CartInterface;

class BillingAddressKey implements RequestPartByQuoteInterface
{
    /**
     * @var ShopperServiceInterface
     */
    private $shopperService;

    /**
     * BillingAddressKey constructor
     *
     * @param ShopperServiceInterface $shopperService
     */
    public function __construct(ShopperServiceInterface $shopperService)
    {
        $this->shopperService = $shopperService;
    }

    /**
     * @inheritDoc
     */
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate
    {
        $shopper = $this->shopperService->createByQuoteAddress($quote->getBillingAddress());
        $orderCreate->setBillingAddressKey($shopper->getAddressKey());

        return $orderCreate;
    }
}

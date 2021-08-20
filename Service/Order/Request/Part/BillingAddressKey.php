<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByOrderInterface;
use CM\Payments\Api\Service\ShopperServiceInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Sales\Api\Data\OrderInterface;

class BillingAddressKey implements RequestPartByOrderInterface
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
    public function process(OrderInterface $order, OrderCreate $orderCreate): OrderCreate
    {
        $shopper = $this->shopperService->createByOrderAddress($order->getBillingAddress());
        $orderCreate->setBillingAddressKey($shopper->getAddressKey());

        return $orderCreate;
    }
}

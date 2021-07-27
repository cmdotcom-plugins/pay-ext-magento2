<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Service\Quote\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByQuoteInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Quote\Api\Data\CartInterface;

class Country implements RequestPartByQuoteInterface
{
    /**
     * @inheritDoc
     */
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate
    {
        $orderCreate->setCountry($quote->getShippingAddress()->getCountryId());

        return $orderCreate;
    }
}

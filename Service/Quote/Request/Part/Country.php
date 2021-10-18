<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

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
        if ($quote->getShippingAddress()) {
            $orderCreate->setCountry($this->getCountry($quote));
        }

        return $orderCreate;
    }

    /**
     * @param CartInterface $quote
     * @return string
     */
    private function getCountry(CartInterface $quote): string
    {
        if ($quote->getShippingAddress()) {
            return $quote->getShippingAddress()->getCountryId();
        }

        return $quote->getCustomer()->getDefaultShipping()->getCountryId();
    }
}

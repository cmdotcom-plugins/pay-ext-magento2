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

class Email implements RequestPartByQuoteInterface
{
    /**
     * @inheritDoc
     */
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate
    {
        $orderCreate->setEmail($this->getEmail($quote));

        return $orderCreate;
    }

    /**
     * @param CartInterface $quote
     * @return string
     */
    private function getEmail(CartInterface $quote): string
    {
        if ($quote->getCustomerIsGuest()) {
            return $quote->getShippingAddress()->getEmail();
        }

        return $quote->getCustomer()->getEmail();
    }
}

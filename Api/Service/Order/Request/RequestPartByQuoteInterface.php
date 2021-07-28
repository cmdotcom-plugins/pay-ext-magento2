<?php

namespace CM\Payments\Api\Service\Order\Request;

use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Quote\Api\Data\CartInterface;

interface RequestPartByQuoteInterface
{
    /**
     * @param CartInterface $quote
     * @param OrderCreate $orderCreate
     *
     * @return OrderCreate
     */
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate;
}

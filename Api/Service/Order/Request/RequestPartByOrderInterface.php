<?php

namespace CM\Payments\Api\Service\Order\Request;

use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Sales\Api\Data\OrderInterface;

interface RequestPartByOrderInterface
{
    /**
     * @param OrderInterface $order
     * @param OrderCreate $orderCreate
     *
     * @return OrderCreate
     */
    public function process(OrderInterface $order, OrderCreate $orderCreate): OrderCreate;
}

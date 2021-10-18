<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByOrderInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Sales\Api\Data\OrderInterface;

class Email implements RequestPartByOrderInterface
{
    /**
     * @inheritDoc
     */
    public function process(OrderInterface $order, OrderCreate $orderCreate): OrderCreate
    {
        $orderCreate->setEmail($this->getEmail($order));

        return $orderCreate;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function getEmail(OrderInterface $order): string
    {
        return $order->getCustomerEmail() ?: $order->getShippingAddress()->getEmail();
    }
}

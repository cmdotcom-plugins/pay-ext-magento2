<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Service\Order\Request\Part;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\Order\Request\RequestPartByOrderInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Sales\Api\Data\OrderInterface;

class PaymentProfile implements RequestPartByOrderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Currency constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function process(OrderInterface $order, OrderCreate $orderCreate): OrderCreate
    {
        $paymentProfile = $this->config->getPaymentProfile($order->getPayment()->getMethod());
        $orderCreate->setPaymentProfile($paymentProfile ?: '');

        return $orderCreate;
    }
}

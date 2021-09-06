<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Request\Part;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\Order\Request\RequestPartByOrderInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

class Expiry implements RequestPartByOrderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Expiry constructor
     *
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
        try {
            $method = $order->getPayment()->getMethod();
            $orderExpiryUnit = $this->config->getOrderExpiryUnit($method);
            $orderExpiryDuration = $this->config->getOrderExpiryDuration($method);

            $expiry = [];
            if ($orderExpiryUnit && $orderExpiryDuration) {
                $expiry = [
                    'expire_after' => [
                        'unit' => $orderExpiryUnit,
                        'duration' => $orderExpiryDuration
                    ]
                ];
            }

            $orderCreate->setExpiry($expiry);
        } catch (NoSuchEntityException $e) {
            $orderCreate->setExpiry([]);
        }

        return $orderCreate;
    }
}

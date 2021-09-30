<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Request\Part;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\Order\Request\RequestPartByQuoteInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Quote\Api\Data\CartInterface;

class PaymentProfile implements RequestPartByQuoteInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * PaymentProfile constructor
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
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate
    {
        $paymentProfile = $this->config->getPaymentProfile();
        $orderCreate->setPaymentProfile($paymentProfile);

        return $orderCreate;
    }
}

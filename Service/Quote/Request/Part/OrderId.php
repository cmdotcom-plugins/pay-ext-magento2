<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByQuoteInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Framework\Math\Random as MathRandom;
use Magento\Quote\Api\Data\CartInterface;

class OrderId implements RequestPartByQuoteInterface
{
    /**
     * @var MathRandom
     */
    private $mathRandom;

    /**
     * OrderId constructor
     *
     * @param MathRandom $mathRandom
     */
    public function __construct(MathRandom $mathRandom)
    {
        $this->mathRandom = $mathRandom;
    }
    /**
     * @inheritDoc
     */
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate
    {
        $orderCreate->setOrderId($this->mathRandom->getUniqueHash('Q_'));

        return $orderCreate;
    }
}

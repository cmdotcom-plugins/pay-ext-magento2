<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Request\OrderCreateRequest;
use Magento\Sales\Api\Data\OrderInterface;

interface OrderRequestBuilderInterface
{
    /**
     * @param OrderInterface $order
     *
     * @return OrderCreateRequest
     */
    public function create(OrderInterface $order): OrderCreateRequest;
}

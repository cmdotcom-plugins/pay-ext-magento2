<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Request\PaymentCreateRequest;
use Magento\Sales\Api\Data\OrderInterface;

interface PaymentRequestBuilderInterface
{
    /**
     * @param OrderInterface $order
     *
     * @return PaymentCreateRequest
     */
    public function create(OrderInterface $order): PaymentCreateRequest;
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use CM\Payments\Client\Request\PaymentCreateRequest;
use Magento\Sales\Api\Data\OrderInterface;

interface PaymentRequestBuilderInterface
{
    /**
     * @param string $orderId
     * @param string $orderKey
     * @param OrderInterface|null $order
     * @param CardDetailsInterface|null $cardDetails
     * @param BrowserDetailsInterface|null $browserDetails
     *
     * @return PaymentCreateRequest
     */
    public function create(
        string $orderId,
        string $orderKey,
        ?OrderInterface $order = null,
        ?CardDetailsInterface $cardDetails = null,
        ?BrowserDetailsInterface $browserDetails = null
    ): PaymentCreateRequest;
}

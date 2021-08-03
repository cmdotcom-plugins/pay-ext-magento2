<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Client\Api\CMPaymentInterface;
use CM\Payments\Exception\EmptyPaymentIdException;
use Magento\Framework\Exception\NoSuchEntityException;

interface PaymentServiceInterface
{
    /**
     * @param string $orderId
     * @return CMPaymentInterface
     * @throws NoSuchEntityException
     * @throws EmptyPaymentIdException
     */
    public function create(string $orderId): CMPaymentInterface;
}

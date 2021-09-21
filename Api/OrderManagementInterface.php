<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api;

use CM\Payments\Client\Api\CMPaymentInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface OrderManagementInterface
 *
 * @api
 */
interface OrderManagementInterface
{

    /**
     * Process Order
     *
     * @param int $orderId
     * @return CMPaymentInterface
     * @throws LocalizedException
     */
    public function processOrder(int $orderId): CMPaymentInterface;
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use Magento\Framework\Exception\NoSuchEntityException;

interface OrderTransactionServiceInterface
{
    /**
     * Process transaction
     *
     * @param string $cmOrderKey
     * @return void
     *
     * @throws NoSuchEntityException
     */
    public function process(string $cmOrderKey): void;
}

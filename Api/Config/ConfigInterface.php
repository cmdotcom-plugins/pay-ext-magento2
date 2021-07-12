<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Config;

interface ConfigInterface
{
    /**
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantKey($storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantName($storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantPassword($storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentProfile($storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getApiMode($storeId = null): string;
}

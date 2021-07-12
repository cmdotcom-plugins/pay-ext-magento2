<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Config;

use CM\Payments\Api\Config\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getMerchantKey($storeId = null): string
    {
        return $this->getValue('payment/cm_payments_general/merchant_key', $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getMerchantName($storeId = null): string
    {
        return $this->getValue('payment/cm_payments_general/merchant_name', $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getMerchantPassword($storeId = null): string
    {
        return $this->getValue('payment/cm_payments_general/merchant_password', $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentProfile($storeId = null): string
    {
        return $this->getValue('payment/cm_payments_methods/profile', $storeId);
    }
    /**
     * @inheritDoc
     */
    public function getApiMode($storeId = null): string
    {
        return $this->getValue('payment/cm_payments_methods/mode', $storeId);
    }

    /**
     * @param $path
     * @param $storeId
     * @return string
     */
    private function getValue(string $path, $storeId): string
    {
        $value = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value ?: '';
    }
}

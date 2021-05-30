<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Config;

use CM\Payments\Api\Config\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encryptor)
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritDoc
     */
    public function getMerchantKey($storeId = null): string
    {
        $merchantPassword = $this->scopeConfig->getValue(
            'payment/cm_payments_general/merchant_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $this->encryptor->decrypt($merchantPassword);
    }

    /**
     * @inheritDoc
     */
    public function getMerchantName($storeId = null): string
    {
        return $this->scopeConfig->getValue(
            'payment/cm_payments_general/merchant_name',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function getMerchantPassword($storeId = null): string
    {
        $merchantPassword = $this->scopeConfig->getValue(
            'payment/cm_payments_general/merchant_password',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $this->encryptor->decrypt($merchantPassword);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentProfile($storeId = null): string
    {
        return $this->scopeConfig->getValue(
            'payment/cm_payments_methods/profile',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Config;

use CM\Payments\Api\Config\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Service constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
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
     * Checks that payment method is active
     *
     * @param string $paymentMethodCode
     * @return ?bool
     * @throws NoSuchEntityException
     */
    public function isPaymentMethodActive(string $paymentMethodCode): ?bool
    {
        return $this->getConfig(
            'payment/' . $paymentMethodCode . '/active',
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId(),
            true
        );
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

    /**
     * Get config value by path
     *
     * @param string $path
     * @param string $scopeType
     * @param string|null $scopeCode
     * @param bool $isFlag
     * @return mixed
     */
    public function getConfig(
        string $path,
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $scopeCode = null,
        bool $isFlag = false
    ) {
        return $isFlag ?
            $this->scopeConfig->isSetFlag($path, $scopeType, $scopeCode) :
            $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }
}

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
    public function getMerchantKey(): ?string
    {
        return $this->getConfig(
            'payment/cm_payments_general/merchant_key',
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getMerchantName(): ?string
    {
        return $this->getConfig(
            'payment/cm_payments_general/merchant_name',
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getMerchantPassword(): ?string
    {
        return $this->getConfig(
            'payment/cm_payments_general/merchant_password',
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getPaymentProfile(): ?string
    {
        return $this->getConfig(
            'payment/cm_payments_methods/profile',
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiMode(): ?string
    {
        return $this->getConfig(
            'payment/cm_payments_methods/mode',
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getCreditCardPaymentProfile(): ?string
    {
        return $this->getConfig(
            'payment/cm_payments_creditcard/profile',
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
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
    private function getConfig(
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

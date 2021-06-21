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
    public function isEnabled(): ?bool
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId(),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function getMerchantKey(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_MERCHANT_KEY,
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
            self::XML_PATH_GENERAL_MERCHANT_NAME,
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
            self::XML_PATH_GENERAL_MERCHANT_PASSWORD,
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
            self::XML_PATH_PAYMENT_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getMode(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_MODE,
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
            self::XML_PATH_PAYMENT_CREDIT_CARD_PROFILE,
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

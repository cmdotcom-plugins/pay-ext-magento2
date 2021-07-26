<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Config;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Model\Adminhtml\Source\Mode;
use CM\Payments\Model\ConfigProvider;
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
    public function getCurrentVersion(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_CURRENT_VERSION,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getMerchantName(): ?string
    {
        $mode = $this->getMode();
        $configPath = self::XML_PATH_GENERAL_TEST_MERCHANT_NAME;

        if ($mode == Mode::LIVE) {
            $configPath = self::XML_PATH_GENERAL_LIVE_MERCHANT_NAME;
        }

        return $this->getConfig(
            $configPath,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getMerchantPassword(): ?string
    {
        $mode = $this->getMode();
        $configPath = self::XML_PATH_GENERAL_TEST_MERCHANT_PASSWORD;

        if ($mode == Mode::LIVE) {
            $configPath = self::XML_PATH_GENERAL_LIVE_MERCHANT_PASSWORD;
        }

        return $this->getConfig(
            $configPath,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getMerchantKey(): ?string
    {
        $mode = $this->getMode();
        $configPath = self::XML_PATH_GENERAL_TEST_MERCHANT_KEY;

        if ($mode == Mode::LIVE) {
            $configPath = self::XML_PATH_GENERAL_LIVE_MERCHANT_KEY;
        }

        return $this->getConfig(
            $configPath,
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
    public function getPaymentProfile(string $paymentMethod): ?string
    {
        $defaultPaymentMethod = $this->getConfig(
            self::XML_PATH_PAYMENT_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );

        if ($paymentMethod == ConfigProvider::CODE_CREDIT_CARD) {
            return $this->getCreditCardPaymentProfile() ?? $defaultPaymentMethod;
        } elseif ($paymentMethod == ConfigProvider::CODE_BANCONTACT) {
            return $this->getBanContactPaymentProfile() ?? $defaultPaymentMethod;
        } elseif ($paymentMethod == ConfigProvider::CODE_CM_PAYMENTS_MENU) {
            return $this->getCmPaymentsMenuPaymentProfile() ?? $defaultPaymentMethod;
        }

        return $defaultPaymentMethod;
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
     * @inheritDoc
     */
    public function getBanContactPaymentProfile(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_PAYMENT_BANCONTACT_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getCmPaymentsMenuPaymentProfile(): ?string
    {
        return $this->getConfig(
            ConfigInterface::XML_PATH_PAYMENT_CM_PAYMENTS_PROFILE,
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

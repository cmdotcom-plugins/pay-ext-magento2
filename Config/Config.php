<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Config;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Model\Adminhtml\Source\MethodMode;
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
    public function isUpdateOnResultPageEnabled(): ?bool
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_UPDATE_ON_RESULT_PAGE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId(),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function isAvailablePaymentMethodsCheckEnabled(): ?bool
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_CHECK_AVAILABLE_PAYMENT_METHODS,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId(),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function isSendOrderEmailForPaid(): bool
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_SEND_ORDER_EMAIL_FOR_PAID,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId(),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function getCustomerSuccessUrl(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_CUSTOM_SUCCESS_URL,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getCustomerErrorUrl(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_CUSTOM_ERROR_URL,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getShippingFeeName(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_SHIPPING_FEE_NAME,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAdjustmentFeeName(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_GENERAL_ADJUSTMENT_FEE_NAME,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function isLogAllRestApiCalls(): bool
    {
        return $this->getConfig(
            self::XML_PATH_PAYMENT_LOG_ALL_API_CALLS,
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
    public function getPaymentProfile(?string $paymentMethod = null): string
    {
        $defaultPaymentMethodProfile = $this->getConfig(
            self::XML_PATH_PAYMENT_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        ) ?? '';

        switch ($paymentMethod) {
            case ConfigProvider::CODE_CREDIT_CARD:
                $paymentMethodProfile = $this->getCreditCardPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            case ConfigProvider::CODE_BANCONTACT:
                $paymentMethodProfile = $this->getBanContactPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            case ConfigProvider::CODE_BELFIUS:
                $paymentMethodProfile = $this->getBelfiusPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            case ConfigProvider::CODE_KBC:
                $paymentMethodProfile = $this->getKbcPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            case ConfigProvider::CODE_CBC:
                $paymentMethodProfile = $this->getCbcPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            case ConfigProvider::CODE_AFTERPAY:
                $paymentMethodProfile = $this->getAfterPayPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            case ConfigProvider::CODE_APPLEPAY:
                $paymentMethodProfile = $this->getApplePayPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            case ConfigProvider::CODE_GIFTCARD:
                $paymentMethodProfile = $this->getAGiftCardPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            case ConfigProvider::CODE_CM_PAYMENTS_MENU:
                $paymentMethodProfile = $this->getCmPaymentsMenuPaymentProfile() ?? $defaultPaymentMethodProfile;
                break;
            default:
                $paymentMethodProfile = $defaultPaymentMethodProfile;
                break;
        }

        return $paymentMethodProfile;
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
    public function getBelfiusPaymentProfile(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_PAYMENT_BELFIUS_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getKbcPaymentProfile(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_PAYMENT_KBC_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getCbcPaymentProfile(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_PAYMENT_CBC_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAfterPayPaymentProfile(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_PAYMENT_AFTERPAY_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getApplePayPaymentProfile(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_PAYMENT_APPLEPAY_PROFILE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAGiftCardPaymentProfile(): ?string
    {
        return $this->getConfig(
            self::XML_PATH_PAYMENT_GIFTCARD_PROFILE,
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
     * @inheritDoc
     */
    public function getOrderExpiryUnit(string $paymentMethodCode): ?string
    {
        $configPath = "payment/{$paymentMethodCode}/order_expiry_unit";
        return $this->getConfig(
            $configPath,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderExpiryDuration(string $paymentMethodCode): ?string
    {
        $configPath = "payment/{$paymentMethodCode}/order_expiry_duration";
        return $this->getConfig(
            $configPath,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getEncryptLibrary(): string
    {
        $baseUrl = 'https://secure.docdatapayments.com/cse/';
        if ($this->getMode() === Mode::TEST) {
            $baseUrl = 'https://testsecure.docdatapayments.com/cse/';
        }

        return $baseUrl . $this->getMerchantKey();
    }

    /**
     * @inheritDoc
     */
    public function getNsa3dsLibrary(): string
    {
        $baseUrl = 'https://secure.docdatapayments.com/ps/script/';
        if ($this->getMode() === Mode::TEST) {
            $baseUrl = 'https://testsecure.docdatapayments.com/ps/script/';
        }

        return $baseUrl . 'nca-3ds-web-sdk.js';
    }

    /**
     * @inheritDoc
     */
    public function isMethodDirect(string $method): bool
    {
        $mode = $this->getConfig(
            'payment/' . $method . '/mode',
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );

        if (empty($mode)) {
            return false;
        }

        return $mode === MethodMode::DIRECT;
    }
    /**
     * @inheritDoc
     */
    public function isCreditCardDirect(): bool
    {
        $mode = $this->getConfig(
            self::XML_PATH_PAYMENT_CREDIT_CARD_MODE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );

        if (empty($mode)) {
            return false;
        }

        return $mode === MethodMode::DIRECT;
    }

    /**
     * @inheritDoc
     */
    public function getCreditCardAllowedTypes(): string
    {
        return $this->getConfig(
            ConfigInterface::XML_PATH_PAYMENT_CREDIT_CARD_ALLOWED_TYPES,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );
    }

    public function getIsKlarnaManualCapture(): bool
    {
        $manualCapture = $this->getConfig(
            self::XML_PATH_PAYMENT_KLARNA_MANUAL_CAPTURE,
            ScopeInterface::SCOPE_STORES,
            (string)$this->storeManager->getStore()->getId()
        );

        return $manualCapture === '1' || $manualCapture === true;
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

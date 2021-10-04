<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Config;

use Magento\Framework\Exception\NoSuchEntityException;

interface ConfigInterface
{
    /**
     * XML Paths of configuration settings
     */
    public const XML_PATH_GENERAL_ENABLED = 'cm_payments/general/enabled';
    public const XML_PATH_GENERAL_CURRENT_VERSION = 'cm_payments/general/current_version';
    public const XML_PATH_GENERAL_TEST_MERCHANT_NAME = 'cm_payments/general/test_merchant_name';
    public const XML_PATH_GENERAL_TEST_MERCHANT_PASSWORD = 'cm_payments/general/test_merchant_password';
    public const XML_PATH_GENERAL_TEST_MERCHANT_KEY = 'cm_payments/general/test_merchant_key';
    public const XML_PATH_GENERAL_LIVE_MERCHANT_NAME = 'cm_payments/general/live_merchant_name';
    public const XML_PATH_GENERAL_LIVE_MERCHANT_PASSWORD = 'cm_payments/general/live_merchant_password';
    public const XML_PATH_GENERAL_LIVE_MERCHANT_KEY = 'cm_payments/general/live_merchant_key';
    public const XML_PATH_GENERAL_MODE = 'cm_payments/general/mode';
    public const XML_PATH_PAYMENT_PROFILE = 'payment/cm_payments_methods/profile';
    public const XML_PATH_PAYMENT_CREDIT_CARD_PROFILE = 'payment/cm_payments_creditcard/profile';
    public const XML_PATH_PAYMENT_BANCONTACT_PROFILE = 'payment/cm_payments_bancontact/profile';
    public const XML_PATH_PAYMENT_CM_PAYMENTS_PROFILE = 'payment/cm_payments/profile';

    /**
     * Checks that extension is enabled
     *
     * @return ?bool
     * @throws NoSuchEntityException
     */
    public function isEnabled(): ?bool;

    /**
     * Get Current Version
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCurrentVersion(): ?string;

    /**
     * Get Merchant Key
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantKey(): ?string;

    /**
     * Get Merchant Name
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantName(): ?string;

    /**
     * Get Merchant Password
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantPassword(): ?string;

    /**
     * @param string|null $paymentMethod
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPaymentProfile(string $paymentMethod = null): string;

    /**
     * Get mode
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMode(): ?string;

    /**
     * Checks that payment method is active
     *
     * @param string $paymentMethodCode
     * @return ?bool
     * @throws NoSuchEntityException
     */
    public function isPaymentMethodActive(string $paymentMethodCode): ?bool;

    /**
     * Get Payment Profile for Credit Card Method
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getCreditCardPaymentProfile(): ?string;

    /**
     * Get Payment Profile for BanContact Method
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getBanContactPaymentProfile(): ?string;

    /**
     * Get Payment Profile for CM Payments Menu Method
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getCmPaymentsMenuPaymentProfile(): ?string;

    /**
     * Get Order Expiry Unit
     *
     * @param string $paymentMethodCode
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getOrderExpiryUnit(string $paymentMethodCode): ?string;

    /**
     * Get Order Expiry Duration
     *
     * @param string $paymentMethodCode
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getOrderExpiryDuration(string $paymentMethodCode): ?string;
}

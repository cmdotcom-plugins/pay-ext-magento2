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
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantKey(): ?string;

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantName(): ?string;

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantPassword(): ?string;

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getPaymentProfile(): ?string;

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getApiMode(): ?string;

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
}

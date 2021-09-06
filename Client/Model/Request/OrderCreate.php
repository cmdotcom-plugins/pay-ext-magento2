<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Request;

class OrderCreate
{
    /**
     * Payment statuses constants
     */
    public const STATUS_SUCCESS = 'success';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ERROR = 'error';

    /**
     * @var string
     */
    private $orderId;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $paymentProfile;

    /**
     * @var array
     */
    private $returnUrls;

    /**
     * @var array
     */
    private $expiry;

    /**
     * @var string
     */
    private $billingAddressKey;

    /**
     * OrderCreate constructor
     *
     * @param ?string $orderId
     * @param ?int $amount
     * @param ?string $currency
     * @param ?string $email
     * @param ?string $language
     * @param ?string $country
     * @param ?string $paymentProfile
     * @param ?array $returnUrls
     * @param ?array $expiry
     * @param ?string $billingAddressKey
     */
    public function __construct(
        ?string $orderId = null,
        ?int $amount = null,
        ?string $currency = null,
        ?string $email = null,
        ?string $language = null,
        ?string $country = null,
        ?string $paymentProfile = null,
        ?array $returnUrls = null,
        ?array $expiry = null,
        ?string $billingAddressKey = null
    ) {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->email = $email;
        $this->language = $language;
        $this->country = $country;
        $this->paymentProfile = $paymentProfile;
        $this->returnUrls = $returnUrls;
        $this->expiry = $expiry;
        $this->billingAddressKey = $billingAddressKey;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'order_reference' => $this->orderId,
            'description' => 'Order ' . $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'email' => $this->email,
            'language' => $this->language,
            'country' => $this->country,
            'profile' => $this->paymentProfile,
            'return_urls' => $this->returnUrls,
            'expiry' => $this->expiry,
            'billing_address_key' => $this->billingAddressKey
        ]);
    }

    /**
     * @return string
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return int
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getPaymentProfile(): ?string
    {
        return $this->paymentProfile;
    }

    /**
     * @param string $paymentProfile
     */
    public function setPaymentProfile(string $paymentProfile): void
    {
        $this->paymentProfile = $paymentProfile;
    }

    /**
     * @return array
     */
    public function getReturnUrls(): ?array
    {
        return $this->returnUrls;
    }

    /**
     * @param array $returnUrls
     */
    public function setReturnUrls(array $returnUrls): void
    {
        $this->returnUrls = $returnUrls;
    }

    /**
     * @return array
     */
    public function getExpiry(): ?array
    {
        return $this->expiry;
    }

    /**
     * @param array $expiry
     */
    public function setExpiry(array $expiry): void
    {
        $this->expiry = $expiry;
    }

    /**
     * @return string
     */
    public function getBillingAddressKey(): ?string
    {
        return $this->billingAddressKey;
    }

    /**
     * @param string $billingAddressKey
     */
    public function setBillingAddressKey(string $billingAddressKey): void
    {
        $this->billingAddressKey = $billingAddressKey;
    }
}

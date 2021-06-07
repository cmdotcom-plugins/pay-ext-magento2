<?php

/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model;

use Magento\Framework\UrlInterface;

class Order
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
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Order constructor
     *
     * @param string $orderId
     * @param int $amount
     * @param string $currency
     * @param string $email
     * @param string $language
     * @param string $country
     * @param string $paymentProfile
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        string $orderId,
        int $amount,
        string $currency,
        string $email,
        string $language,
        string $country,
        string $paymentProfile,
        UrlInterface $urlBuilder
    ) {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->email = $email;
        $this->language = $language;
        $this->country = $country;
        $this->paymentProfile = $paymentProfile;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'order_reference' => $this->orderId,
            'description' => 'Order ' . $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'email' => $this->email,
            'language' => $this->language,
            'country' => $this->country,
            'profile' => $this->paymentProfile,
            'return_urls' => [
                'success' => $this->getReturnUrl($this->orderId, self::STATUS_SUCCESS),
                'pending' => $this->getReturnUrl($this->orderId, self::STATUS_PENDING),
                'cancelled' => $this->getReturnUrl($this->orderId, self::STATUS_CANCELLED),
                'error' => $this->getReturnUrl($this->orderId, self::STATUS_ERROR),
            ]
        ];
    }

    /**
     * Get Return Url
     *
     * @param string $orderReference
     * @param string $status
     * @return string
     */
    private function getReturnUrl(string $orderReference, string $status): string
    {
        return $this->urlBuilder->getUrl('cmpayments/payment/result', [
            '_query' => [
                'order_reference' => $orderReference,
                'status' => $status
            ]
        ]);
    }
}

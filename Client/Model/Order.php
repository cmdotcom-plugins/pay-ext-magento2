<?php
/**
 * Copyright © 2021 cm.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model;

class Order
{
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
     * Order constructor.
     * @param string $orderId
     * @param int $amount
     * @param string $currency
     * @param string $email
     * @param string $language
     * @param string $country
     * @param string $paymentProfile
     */
    public function __construct(
        string $orderId,
        int $amount,
        string $currency,
        string $email,
        string $language,
        string $country,
        string $paymentProfile
    ) {

        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->email = $email;
        $this->language = $language;
        $this->country = $country;
        $this->paymentProfile = $paymentProfile;
    }

    /**
     * convert object to array
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
        ];
    }
}

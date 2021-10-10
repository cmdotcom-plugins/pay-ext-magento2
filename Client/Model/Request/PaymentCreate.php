<?php

/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Request;

class PaymentCreate
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $idealDetails;

    /**
     * @var array
     */
    private $elvDetails;

    /**
     * @var CardDetails
     */
    private $cardDetails;

    /**
     * Order constructor
     *
     * @param string $method
     * @param array $idealDetails
     * @param array $elvDetails
     * @param CardDetails|null $cardDetails
     */
    public function __construct(
        string $method = '',
        array $idealDetails = [],
        array $elvDetails = [],
        CardDetails $cardDetails = null
    ) {
        $this->method = $method;
        $this->idealDetails = $idealDetails;
        $this->elvDetails = $elvDetails;
        $this->cardDetails = $cardDetails;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'method' => $this->method
        ];

        if ($this->idealDetails) {
            $data['ideal_details'] = $this->idealDetails;
        }

        if ($this->elvDetails) {
            $data['elv_payment_input'] = $this->elvDetails;
        }

        if ($this->cardDetails) {
            $data['card_details'] = $this->cardDetails->toArray();
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getIdealDetails(): array
    {
        return $this->idealDetails;
    }

    /**
     * @param array $idealDetails
     */
    public function setIdealDetails(array $idealDetails): void
    {
        $this->idealDetails = $idealDetails;
    }

    /**
     * @return array
     */
    public function getElvDetails(): array
    {
        return $this->elvDetails;
    }

    /**
     * @param array $elvDetails
     */
    public function setElvDetails(array $elvDetails): void
    {
        $this->elvDetails = $elvDetails;
    }

    /**
     * @return CardDetails
     */
    public function getCardDetails(): CardDetails
    {
        return $this->cardDetails;
    }

    /**
     * @param CardDetails $cardDetails
     */
    public function setCardDetails(CardDetails $cardDetails): void
    {
        $this->cardDetails = $cardDetails;
    }
}

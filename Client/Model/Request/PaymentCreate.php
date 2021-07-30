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
     * Order constructor
     *
     * @param string $method
     * @param array $idealDetails
     * @param array $elvDetails
     */
    public function __construct(
        string $method = '',
        array $idealDetails = [],
        array $elvDetails = []
    ) {
        $this->method = $method;
        $this->idealDetails = $idealDetails;
        $this->elvDetails = $elvDetails;
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
            $data['ideal_details'] =  $this->idealDetails;
        }

        if ($this->elvDetails) {
            $data['elv_payment_input'] =  $this->elvDetails;
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
        $this->idealDetails = $elvDetails;
    }
}

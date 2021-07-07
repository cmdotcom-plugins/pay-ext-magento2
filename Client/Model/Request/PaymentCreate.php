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
     * Order constructor
     *
     * @param string $method
     * @param array $idealDetails
     */
    public function __construct(
        string $method = '',
        array $idealDetails = []
    ) {
        $this->method = $method;
        $this->idealDetails = $idealDetails;
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
}

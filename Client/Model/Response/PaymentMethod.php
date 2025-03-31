<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response;

class PaymentMethod
{
    /**
     * @var string|null
     */
    private $method;

    /**
     * PaymentMethod constructor
     *
     * @param array $method
     */
    public function __construct(
        array $method
    ) {
        $this->method = $method['method'];
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}

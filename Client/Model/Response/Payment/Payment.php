<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response\Payment;

class Payment
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $method;

    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * Payment constructor.
     * @param array $payment
     */
    public function __construct(
        array $payment
    ) {
        $this->id = $payment['id'];
        $this->method = $payment['method'];

        $this->authorization = $payment['authorization'] ? new Authorization($payment['authorization']) : null;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return ?Authorization
     */
    public function getAuthorization(): ?Authorization
    {
        return $this->authorization;
    }
}

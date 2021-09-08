<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model;

class CMPaymentUrl
{
    /** URL type */
    public const PURPOSE_REDIRECT = 'REDIRECT';

    /**
     * @var string
     */
    private $purpose;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $order;

    /**
     * CMPaymentUrl constructor
     *
     * @param string $purpose
     * @param string $method
     * @param string $url
     * @param string $order
     */
    public function __construct(
        string $purpose,
        string $method,
        string $url,
        string $order
    ) {
        $this->purpose = $purpose;
        $this->method = $method;
        $this->url = $url;
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPurpose(): string
    {
        return $this->purpose;
    }
}

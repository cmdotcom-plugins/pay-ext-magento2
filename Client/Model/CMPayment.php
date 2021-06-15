<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model;

use CM\Payments\Client\Api\CMPaymentInterface;

class CMPayment implements CMPaymentInterface
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $status;
    /**
     * @var CMPaymentInterface[]
     */
    private $urls;

    /**
     * CMPayment constructor.
     * @param string $id
     * @param string $status
     * @param CMPaymentInterface[] $urls
     */
    public function __construct(string $id, string $status, array $urls = [])
    {
        $this->id = $id;
        $this->status = $status;
        $this->urls = $urls;
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
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return CMPaymentInterface[]
     */
    public function getUrls(): array
    {
        return $this->urls;
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response;

class RefundResponse
{
    /**
     * @var mixed|null
     */
    private $merchantRequestKey;
    /**
     * @var mixed|null
     */
    private $amount;
    /**
     * @var mixed|null
     */
    private $currency;
    /**
     * @var array
     */
    private $refund;

    /**
     * Refund Response constructor
     *
     * @param array $refund
     */
    public function __construct(
        array $refund
    ) {
        $this->refund = $refund;
        $this->merchantRequestKey = $refund['merchant_request_key'] ?? null;
        $this->amount = $refund['amount'] ?? null;
        $this->currency = $refund['currency'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getMerchantRequestKey()
    {
        return $this->merchantRequestKey;
    }

    /**
     * @return mixed|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    public function toArray(): array
    {
        return $this->refund;
    }
}

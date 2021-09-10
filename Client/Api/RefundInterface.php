<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

use CM\Payments\Client\Model\Request\RefundCreate;
use CM\Payments\Client\Model\Response\RefundResponse;
use GuzzleHttp\Exception\RequestException;

interface RefundInterface
{
    /**
     * @param RefundCreate $refundCreate
     * @return RefundResponse
     *
     * @throws RequestException
     */
    public function refund(RefundCreate $refundCreate): RefundResponse;
}

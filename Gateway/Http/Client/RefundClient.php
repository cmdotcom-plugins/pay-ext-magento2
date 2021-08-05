<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Gateway\Http\Client;

use CM\Payments\Client\Model\Request\RefundCreate;
use CM\Payments\Client\Refund;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class RefundClient implements ClientInterface
{
    /**
     * @var Refund
     */
    private $refundClient;

    /**
     * RefundClient constructor.
     *
     * @param Refund $refundClient
     */
    public function __construct(
        Refund $refundClient
    ) {
        $this->refundClient = $refundClient;
    }
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();

        /** @var RefundCreate $refundCreate */
        $refundCreate = $request['payload'];

        $response = $this->refundClient->refund($refundCreate);

        return $response->toArray();
    }
}

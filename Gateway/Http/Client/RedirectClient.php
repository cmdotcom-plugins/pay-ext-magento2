<?php
/**
 * Copyright © 2021 cm.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class RedirectClient implements ClientInterface
{
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        return [];
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\ResourceModel\Payment;

use CM\Payments\Model\Payment as CMPayment;
use CM\Payments\Model\ResourceModel\Payment as CMPaymentResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            CMPayment::class,
            CMPaymentResource::class
        );
    }
}

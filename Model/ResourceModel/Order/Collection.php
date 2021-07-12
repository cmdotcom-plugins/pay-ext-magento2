<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\ResourceModel\Order;

use CM\Payments\Model\Order as CMOrder;
use CM\Payments\Model\ResourceModel\Order as CMOrderResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            CMOrder::class,
            CMOrderResource::class
        );
    }
}

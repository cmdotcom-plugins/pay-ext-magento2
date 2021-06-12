<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\ResourceModel;

use CM\Payments\Api\Model\Data\OrderInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(OrderInterface::TABLE_NAME, OrderInterface::ID);
    }
}

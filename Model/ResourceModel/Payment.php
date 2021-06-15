<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\ResourceModel;

use CM\Payments\Api\Model\Data\PaymentInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Payment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(PaymentInterface::TABLE_NAME, PaymentInterface::ID);
    }
}

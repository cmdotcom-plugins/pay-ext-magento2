<?php
/**
 * Copyright © 2021 cm.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Model\ResourceModel\Order as ResourceOrder;

class Order extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'cm_payments_order';

    protected function _construct()
    {
        $this->_init(ResourceOrder::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}

<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Adminhtml\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    protected $_allowedTypes = ['VI', 'MC', 'AE', 'MI', 'MD'];
}

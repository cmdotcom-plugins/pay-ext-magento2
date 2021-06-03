<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * Live/Test Key Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => 'live', 'label' => __('Live')],
                ['value' => 'test', 'label' => __('Test')]
            ];
        }
        return $this->options;
    }
}

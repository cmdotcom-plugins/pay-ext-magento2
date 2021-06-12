<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Model\AdminHtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    const TEST = 'test';
    const PROD = 'production';

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
                ['value' => self::TEST, 'label' => __('Test')],
                ['value' => self::PROD, 'label' => __('Production')]
            ];
        }
        return $this->options;
    }
}

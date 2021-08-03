<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OrderExpiryUnit implements OptionSourceInterface
{
    /**
     * Units
     */
    public const MINUTES = 'MINUTES';
    public const HOURS = 'HOURS';
    public const DAYS = 'DAYS';

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * Test/Live Key Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => self::MINUTES, 'label' => __('Minutes')],
                ['value' => self::HOURS, 'label' => __('Hours')],
                ['value' => self::DAYS, 'label' => __('Days')]
            ];

            array_unshift($this->options, ['value' => '', 'label' => __('None')]);
        }

        return $this->options;
    }
}

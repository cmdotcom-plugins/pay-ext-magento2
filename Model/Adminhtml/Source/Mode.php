<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    /**
     * Modes
     */
    public const TEST = 'test';
    public const LIVE = 'live';

    /**
     * Options array
     *
     * @var ?array
     */
    public ?array $options = null;

    /**
     * Test/Live Key Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => self::TEST, 'label' => __('Test')],
                ['value' => self::LIVE, 'label' => __('Live')]
            ];
        }

        return $this->options;
    }
}

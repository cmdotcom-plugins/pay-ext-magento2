<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MethodMode implements OptionSourceInterface
{
    /**
     * Modes
     */
    public const REDIRECT = 'redirect';
    public const DIRECT = 'direct';

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
                ['value' => self::REDIRECT, 'label' => __('Redirect to Menu')],
                ['value' => self::DIRECT, 'label' => __('Direct')]
            ];
        }

        return $this->options;
    }
}

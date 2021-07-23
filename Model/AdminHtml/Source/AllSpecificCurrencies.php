<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CM\Payments\Model\AdminHtml\Source;

class AllSpecificCurrencies implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('All Allowed Currencies')],
            ['value' => 1, 'label' => __('Specific Currencies')]
        ];
    }
}

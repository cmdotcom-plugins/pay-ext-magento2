<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Currency implements OptionSourceInterface
{
    /**
     * Countries
     *
     * @var \Magento\Directory\Model\Currency
     */
    protected $currencyModel;

    /**
     * Options array
     *
     * @var array
     */
    protected $options;

    /**
     * @param \Magento\Directory\Model\Currency $currency
     */
    public function __construct(\Magento\Directory\Model\Currency $currency)
    {
        $this->currencyModel = $currency;
    }

    /**
     * Return options array
     *
     * @param bool $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray($isMultiselect = false, $foregroundCountries = '')
    {
        if (!$this->options) {
            $this->options = array_map(function ($currency) {
                return ['value' => $currency, 'label' => $currency];
            }, $this->currencyModel->getConfigAllowCurrencies());
        }

        $options = $this->options;

        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }
}

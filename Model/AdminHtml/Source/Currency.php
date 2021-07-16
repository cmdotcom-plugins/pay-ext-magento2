<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CM\Payments\Model\AdminHtml\Source;

class Currency implements \Magento\Framework\Option\ArrayInterface
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
     * @param boolean $isMultiselect
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

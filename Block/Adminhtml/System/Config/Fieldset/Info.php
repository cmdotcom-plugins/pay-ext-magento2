<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Info extends Fieldset
{
    /**
     * Add custom css class
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element) . ' with-button';
    }

    /**
     * Return header title part of html for payment solution
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $html = '<div class="config-heading" >';
        $htmlId = $element->getHtmlId();
        $html .= '<div class="button-container"><button type="button"' .
            ' disabled="disabled"' .
            ' class="button action-configure' .
            ' disabled' .
            '" id="' . $htmlId . '-head" onclick="cmPaymentsOpenSolution.call(this, \'' .
            $htmlId .
            "', '" .
            $this->getUrl(
                'adminhtml/system_config/edit/section/cm_payments_general'
            ) . '\'); return false;">' .
            '<span class="state-closed">' . __(
                'Configure'
            ) . '</span></button>';

        $html .= '</div>';
        $html .= '<div class="heading"><strong>' . $element->getLegend() . '</strong>';

        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }

        $html .= '<div class="config-alt"></div></div></div>';

        return $html;
    }

    /**
     * Return header comment part of html for payment solution
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param AbstractElement $element
     * @return false
     */
    protected function _isCollapseState($element)
    {
        return false;
    }

    /**
     * Return extra Js
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getExtraJs($element)
    {
        $script = "window.cmPaymentsOpenSolution = function (id, url) {
            window.location.href = url;
            event.preventDefault();
        }";

        return $this->_jsHelper->getScript($script);
    }
}

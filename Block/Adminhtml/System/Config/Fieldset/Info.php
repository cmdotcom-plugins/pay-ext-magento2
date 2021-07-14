<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Info extends Fieldset
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param array $data
     * @param SecureHtmlRenderer $secureRenderer
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        SecureHtmlRenderer $secureRenderer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $authSession,
            $jsHelper,
            $data,
            $secureRenderer
        );

        $this->secureRenderer = $secureRenderer;
    }

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
            '" id="' . $htmlId . '-head" >' .
            '<span class="state-closed">' . __(
                'Configure'
            ) . '</span></button>';

        $html .= $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            "window.location.href='"
            . $this->getUrl('adminhtml/system_config/edit/section/cm_payments_general')
            . "';event.preventDefault();",
            'button#' . $htmlId . '-head'
        );

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
}

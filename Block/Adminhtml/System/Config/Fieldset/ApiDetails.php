<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ApiDetails extends Fieldset
{
    /**
     * Return header html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        return parent::_getHeaderHtml($element) .
            '<div class="api-details-extra" ' .
            'data-mage-init=\'{"CM_Payments/js/system/action/api-details-config":' .
            '{"modeContainerSelector": "#cm_payments_general_api_details_mode"}}\'></div>';
    }
}

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
     * Return extra Js
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getExtraJs($element)
    {
        $initialScript = parent::_getExtraJs($element);
        $extendedScript = "require(['jquery'], function ($) {
            $('#cm_payments_general_api_details_mode').change(function () {
                let modeContainerId = $(this).closest('tr').attr('id'),
                    parentTable = $(this).parents().find('table'),
                    mode = $(this).val();
                parentTable.find('tr').each(function () {
                    let containerId = $(this).attr('id');
                    if (containerId !== modeContainerId) {
                        if (containerId.indexOf('_' + mode + '_') == -1) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    }
                });
            });
            
            $('#cm_payments_general_api_details_mode').trigger('change');
        });";

        return $initialScript . $this->_jsHelper->getScript($extendedScript);
    }
}

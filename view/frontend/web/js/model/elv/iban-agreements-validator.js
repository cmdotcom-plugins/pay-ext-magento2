/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    let ibanAgreementsInputPath = '.payment-method._active div.iban-agreements input';

    return {
        /**
         * Validate ELV IBAN agreements
         *
         * @returns {Boolean}
         */
        validate: function (hideError) {
            let isValid = true;

            $(ibanAgreementsInputPath).each(function (index, element) {
                if (!$.validator.validateSingleElement(element, {
                    errorElement: 'div',
                    hideError: hideError || false
                })) {
                    isValid = false;
                }
            });

            return isValid;
        }
    };
});

/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
    'mage/translate'
], function (
    $,
    creditCardNumberValidator
) {
    'use strict';

    $.each({
        'validate-allowed-card-type': [
            function (number, item, allowedTypes) {
                let cardInfo,
                    i,
                    l;

                if (!creditCardNumberValidator(number).isValid) {
                    return false;
                }

                cardInfo = creditCardNumberValidator(number).card;

                for (i = 0, l = allowedTypes.length; i < l; i++) {
                    if (cardInfo.type == allowedTypes[i].type.value) {
                        return true;
                    }
                }

                return false;
            },
            $.mage.__('Please enter a valid credit card type number.')
        ]
    }, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
});

/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'CM_Payments/js/model/validators/creditcard/card-number-validator',
    'mage/translate'
], function (
    $,
    creditCardNumberValidator
) {
    'use strict';

    $.each({
        'validate-allowed-card-type': [
            function (number, item, allowedTypes) {
                let cardInfo;

                if (!creditCardNumberValidator(number, allowedTypes).isValid) {
                    return false;
                }

                cardInfo = creditCardNumberValidator(number, allowedTypes).card;
                for (let i = 0; i < allowedTypes.length; i++) {
                    if (cardInfo.type == allowedTypes[i]) {
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

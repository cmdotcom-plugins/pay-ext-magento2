/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'mageUtils',
    'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator/luhn10-validator',
    'CM_Payments/js/model/validators/creditcard/card-type-validator'
], function (
    utils,
    luhn10,
    creditCardTypeValidator
) {
    'use strict';

    /**
     * @param {*} card
     * @param {*} isPotentiallyValid
     * @param {*} isValid
     * @return {Object}
     */
    function resultWrapper(card, isPotentiallyValid, isValid) {
        return {
            card: card,
            isValid: isValid,
            isPotentiallyValid: isPotentiallyValid
        };
    }

    return function (value, allowedTypes) {
        let potentialTypes,
            cardType,
            valid,
            maxLength;

        if (utils.isEmpty(value)) {
            return resultWrapper(null, false, false);
        }

        value = value.replace(/\s+/g, '');

        if (!/^\d*$/.test(value)) {
            return resultWrapper(null, false, false);
        }

        potentialTypes = creditCardTypeValidator.getCardTypes(value);
        for (let i = 0; i < potentialTypes.length; i++) {
            if (allowedTypes.indexOf(potentialTypes[i].type) == -1) {
                potentialTypes.splice(i, 1);
            }
        }

        if (potentialTypes.length === 0) {
            return resultWrapper(null, false, false);
        }

        cardType = potentialTypes[0];
        if (cardType.type === 'VP') {  // V-Pay is not Luhn 10 compliant
            valid = true;
        } else {
            valid = luhn10(value);
        }

        for (let j = 0; j < cardType.lengths.length; j++) {
            if (cardType.lengths[j] === value.length) {
                return resultWrapper(cardType, valid, valid);
            }
        }

        maxLength = Math.max.apply(null, cardType.lengths);

        if (value.length < maxLength) {
            return resultWrapper(cardType, true, false);
        }

        return resultWrapper(cardType, false, false);
    };
});

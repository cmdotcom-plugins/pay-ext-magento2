/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'mageUtils'
], function (
    $,
    utils
) {
    'use strict';

    let types = [
        {
            title: 'Visa',
            type: 'VI',
            pattern: '^4\\d*$',
            gaps: [4, 8, 12],
            lengths: [16],
            code: {
                name: 'CVV',
                size: 3
            }
        },
        {
            title: 'V-Pay',
            type: 'VP',
            pattern: '^4\\d*$',
            gaps: [4, 8, 12],
            lengths: [16, 19],
            code: {
                name: 'CVV',
                size: 3
            }
        },
        {
            title: 'MasterCard',
            type: 'MC',
            pattern: '^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$',
            gaps: [4, 8, 12],
            lengths: [16],
            code: {
                name: 'CVC',
                size: 3
            }
        },
        {
            title: 'American Express',
            type: 'AE',
            pattern: '^3([47]\\d*)?$',
            isAmex: true,
            gaps: [4, 10],
            lengths: [15],
            code: {
                name: 'CID',
                size: 4
            }
        },
        {
            title: 'Maestro International',
            type: 'MI',
            pattern: '^(5(0|[6-9])|63|67(?!59|6770|6774))\\d*$',
            gaps: [4, 8, 12],
            lengths: [12, 13, 14, 15, 16, 17, 18, 19],
            code: {
                name: 'CVC',
                size: 3
            }
        },
        {
            title: 'Maestro Domestic',
            type: 'MD',
            pattern: '^6759(?!24|38|40|70|76)|676770|676774\\d*$',
            gaps: [4, 8, 12],
            lengths: [12, 13, 14, 15, 16, 17, 18, 19],
            code: {
                name: 'CVC',
                size: 3
            }
        },
    ];

    return {
        /**
         * @param {*} cardNumber
         * @return {Array}
         */
        getCardTypes: function (cardNumber) {
            let i,
                value,
                result = [];

            if (utils.isEmpty(cardNumber)) {
                return result;
            }

            if (cardNumber === '') {
                return $.extend(true, {}, types);
            }

            for (i = 0; i < types.length; i++) {
                value = types[i];

                if (new RegExp(value.pattern).test(cardNumber)) {
                    result.push($.extend(true, {}, value));
                }
            }

            return result;
        }
    };
});

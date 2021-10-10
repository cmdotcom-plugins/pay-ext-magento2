/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'CM_Payments/js/view/payment/method-renderer/creditcard'
], function (
    Component
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'CM_Payments/payment/vpay'
        }
    });
});

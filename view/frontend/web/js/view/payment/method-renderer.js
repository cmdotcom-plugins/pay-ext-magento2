/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (
    $,
    Component,
    rendererList
) {
    'use strict';

    let defaultComponent = 'CM_Payments/js/view/payment/method-renderer/cm_payments',
        bancontactComponent = 'CM_Payments/js/view/payment/method-renderer/bancontact',
        isEnabled = window.checkoutConfig.payment.cm_payments.is_enabled,
        methods = [
            {type: 'cm_payments', component: defaultComponent},
            {type: 'cm_payments_creditcard', component: defaultComponent},
            {type: 'cm_payments_ideal', component: defaultComponent},
            {type: 'cm_payments_paypal', component: defaultComponent},
            {type: 'cm_payments_bancontact', component: bancontactComponent}
        ];

    $.each(methods, function (k, method) {
        if (isEnabled) {
            rendererList.push(method);
        }
    });

    return Component.extend({});
});

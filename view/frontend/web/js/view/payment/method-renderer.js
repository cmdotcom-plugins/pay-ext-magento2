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

    let isEnabled = window.checkoutConfig.payment.cm_payments.is_enabled,
        defaultComponent = 'CM_Payments/js/view/payment/method-renderer/cm_payments',
        creditcardComponent = 'CM_Payments/js/view/payment/method-renderer/creditcard',
        maestroComponent = 'CM_Payments/js/view/payment/method-renderer/maestro',
        vpayComponent = 'CM_Payments/js/view/payment/method-renderer/vpay',
        idealComponent = 'CM_Payments/js/view/payment/method-renderer/ideal',
        paypalComponent = 'CM_Payments/js/view/payment/method-renderer/paypal',
        elvComponent = 'CM_Payments/js/view/payment/method-renderer/elv',
        klarnaComponent = 'CM_Payments/js/view/payment/method-renderer/klarna',
        methods = [
            {type: 'cm_payments', component: defaultComponent},
            {type: 'cm_payments_creditcard', component: creditcardComponent},
            {type: 'cm_payments_maestro', component: maestroComponent},
            {type: 'cm_payments_vpay', component: vpayComponent},
            {type: 'cm_payments_ideal', component: idealComponent},
            {type: 'cm_payments_paypal', component: paypalComponent},
            {type: 'cm_payments_bancontact', component: defaultComponent},
            {type: 'cm_payments_elv', component: elvComponent},
            {type: 'cm_payments_klarna', component: klarnaComponent},
            {type: 'cm_payments_afterpay', component: defaultComponent},
            {type: 'cm_payments_applepay', component: defaultComponent},
            {type: 'cm_payments_giftcard', component: defaultComponent},
        ];

    $.each(methods, function (k, method) {
        if (isEnabled) {
            rendererList.push(method);
        }
    });

    return Component.extend({});
});

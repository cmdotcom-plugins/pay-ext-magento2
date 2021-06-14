/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'cm_payments',
                component: 'CM_Payments/js/view/payment/method-renderer/cm_payments'
            }
        );

        return Component.extend({});
    }
);

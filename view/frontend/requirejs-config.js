/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'CM_Payments/js/model/shipping-save-processor/default-mixin': true
            },
            'Magento_Checkout/js/action/get-payment-information': {
                'CM_Payments/js/action/get-payment-information-mixin': true
            }
        }
    },
    map: {
        '*': {
            'creditcard-3dsv2-validation': 'CM_Payments/js/model/creditcard/3dsv2-validation',
            'creditcard-additional-validators': 'CM_Payments/js/model/creditcard/additional-validators',
            'creditcard-init-payment': 'CM_Payments/js/action/creditcard/init-payment-information'
        }
    }
};

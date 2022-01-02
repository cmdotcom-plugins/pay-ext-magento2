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
        }
    }
};

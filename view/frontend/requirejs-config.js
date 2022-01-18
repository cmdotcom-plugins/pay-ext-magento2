/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

var config = {
    config: {
        mixins: {
            // We need to save the guestEmail to the quote to get the CM.com payment methods by quote
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'CM_Payments/js/model/shipping-save-processor/default-mixin': true
            },
        }
    }
};

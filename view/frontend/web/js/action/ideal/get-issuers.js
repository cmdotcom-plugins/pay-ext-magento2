/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer'
], function (
    urlBuilder,
    storage,
    quote,
    customer
) {
    'use strict';

    /**
     * This method creates a CM.com payment based on the encrypted card details and orderId
     * @return {Deferred}
     */
    return function () {
        var serviceUrl;

        /**
         * Checkout for guest and registered customer.
         */
        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/cmpayments/get-iban-issuers', {
                cartId: quote.getQuoteId()
            });
        } else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/cmpayments/get-iban-issuers', {});
        }

        return storage.post(
            serviceUrl
        );
    };
});

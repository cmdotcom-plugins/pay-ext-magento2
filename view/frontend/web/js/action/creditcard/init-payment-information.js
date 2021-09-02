/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    quote,
    urlBuilder,
    storage,
    errorProcessor,
    customer,
    loader
) {
    'use strict';

    return function (messageContainer, paymentData) {
        let serviceUrl,
            payload = {
                quoteId: quote.getQuoteId(),
                cardDetails: {
                    'method': paymentData.additional_data.cc_type,
                    'encrypted_card_data': paymentData.additional_data.data
                },
                browserDetails: {
                    'user_agent': navigator.userAgent
                }
            };

        /**
         * Checkout for guest and registered customer.
         */
        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/cmpayments/guest-payment/credit-card/:quoteId', {
                quoteId: quote.getQuoteId()
            });
        } else {
            serviceUrl = urlBuilder.createUrl('/cmpayments/mine-payment/credit-card', {});
        }

        loader.startLoader();
        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        ).fail(
            function (response) {
                errorProcessor.process(response, messageContainer);
            }
        ).always(
            function () {
                loader.stopLoader();
            }
        );
    };
});

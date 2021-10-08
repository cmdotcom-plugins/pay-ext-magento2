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
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    urlBuilder,
    storage,
    errorProcessor,
    customer,
    loader
) {
    'use strict';

    /**
     * This method creates a CM.com payment based on the encrypted card details and orderId
     * @param {object} messageContainer
     * @param {object} paymentData
     * @param {string} orderId
     * @return {Deferred}
     */
    return function (messageContainer, paymentData, orderId) {
        const payload = {
            cardDetails: {
                'method': paymentData.additional_data.cc_type,
                'encrypted_card_data': paymentData.additional_data.data
            },
            browserDetails: {
                'user_agent': navigator.userAgent
            }
        };

        const serviceUrl = urlBuilder.createUrl('/cmpayments/payment/credit-card/:orderId', {
            orderId: parseInt(orderId)
        });

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

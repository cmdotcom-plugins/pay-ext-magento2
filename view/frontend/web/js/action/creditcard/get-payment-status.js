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
    'Magento_Checkout/js/model/error-processor'
], function (
    quote,
    urlBuilder,
    storage
) {
    'use strict';

    return {
        pollingTimeout: 5 * 60 * 1000,
        interval: 5000,
        statuses: ['pending', 'new'],

        /**
         * Get order status by payment id
         * @param paymentId
         * @returns {Promise<{order_id: string, status: string}>}
         */
        get: function (paymentId) {
            return storage.get(this.getServiceUrl(paymentId))
        },

        /**
         *
         * @param {string} paymentId
         * @param {int} interval
         * @param {int} timeout
         * @returns {Promise<{order_id: string, status: string}>}
         */
        pollingStatus: function (paymentId, interval, timeout) {
            this.interval = interval || this.interval;
            this.pollingTimeout = timeout || this.pollingTimeout;

            return this.getStatusChange(paymentId);
        },

        /**
         * Get url
         * @param {string} paymentId
         * @returns {{resource: *, prefix: string, suffix: string}|{resource: *, prefix: *, suffix: *}|*}
         */
        getServiceUrl: function(paymentId) {
            return urlBuilder.createUrl('/cmpayments/payment/credit-card/status/:paymentId', {
                paymentId: paymentId
            });
        },

        /**
         * Polling the order status of Magento and wait for statuses other then pending or new.
         * @param {string} paymentId
         * @returns {Promise<{order_id: string, status: string}>}
         */
        getStatusChange: function (paymentId) {
            const self = this;

            return new Promise(function (resolve, reject) {
                (function waitForStatusChange() {

                    setTimeout(function () {
                        storage.get(self.getServiceUrl(paymentId)).done(function (response) {
                            if (self.statuses.indexOf(response.status) === -1) {
                                return resolve(response);
                            }

                            waitForStatusChange();
                        }).fail(
                            function (response) {
                                console.error(response);
                            }
                        );
                    }, self.interval)

                    // Reject promise if polling timeout exceed.
                    setTimeout(() => {
                        reject('Timed out in ' + self.pollingTimeout + 'ms.')
                    }, self.pollingTimeout);

                })();
            });
        }
    }
});

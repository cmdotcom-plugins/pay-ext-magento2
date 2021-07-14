/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'mage/utils/wrapper',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/payment-service',
    'underscore'
], function (
    wrapper,
    $,
    quote,
    urlBuilder,
    storage,
    errorProcessor,
    customer,
    methodConverter,
    paymentService,
    _
) {
    'use strict';

    return function (getPaymentInformationFunction) {
        return wrapper.wrap(getPaymentInformationFunction, function (
            originalGetPaymentInformationFunction,
            deferred,
            messageContainer
        ) {
            //TODO: Use Original function in case of module disabled
            //originalGetPaymentInformationFunction(deferred, messageContainer);
            let serviceUrl;

            deferred = deferred || $.Deferred();

            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/payment-information', {
                    cartId: quote.getQuoteId()
                });
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
            }

            return storage.get(
                serviceUrl,
                false
            ).done(function (response) {
                quote.setTotals(response.totals);
                let methods = methodConverter(response['payment_methods']);
                if (typeof response['extension_attributes'] !== 'undefined') {
                    _.each(methods, function (method) {
                        if (typeof response['extension_attributes'][method.method] !== 'undefined') {
                            _.each(response['extension_attributes'][method.method], function (data, property) {
                                window.checkoutConfig.payment[method.method][property] = data;
                            });
                        }
                    });
                }

                paymentService.setPaymentMethods(methods);
                deferred.resolve();
            }).fail(function (response) {
                errorProcessor.process(response, messageContainer);
                deferred.reject();
            });
        });
    };
});
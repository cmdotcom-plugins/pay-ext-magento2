/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender',
    'underscore'
], function (
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    fullScreenLoader,
    selectBillingAddressAction,
    payloadExtender,
    _
) {
    'use strict';

    return function (defaultProcessor) {
        //TODO: Use Original function in case of module disabled (replacing on wrapper)
        defaultProcessor.saveShippingInformation = function () {
            let payload;

            if (!quote.billingAddress() && quote.shippingAddress().canUseForBilling()) {
                selectBillingAddressAction(quote.shippingAddress());
            }

            if (quote.guestEmail) {
                let shippingAddress = quote.shippingAddress();
                shippingAddress.email = quote.guestEmail;
                quote.shippingAddress(shippingAddress);
            }

            payload = {
                addressInformation: {
                    'shipping_address': quote.shippingAddress(),
                    'billing_address': quote.billingAddress(),
                    'shipping_method_code': quote.shippingMethod()['method_code'],
                    'shipping_carrier_code': quote.shippingMethod()['carrier_code']
                }
            };

            payloadExtender(payload);

            fullScreenLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForSetShippingInformation(quote),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    quote.setTotals(response.totals);
                    let methods = methodConverter(response['payment_methods']);
                    _.each(methods, function (method) {
                        if (typeof response['extension_attributes'][method.method] !== 'undefined') {
                            _.each(response['extension_attributes'][method.method], function (data, property) {
                                window.checkoutConfig.payment[method.method][property] = data;
                            });
                        }
                    });

                    paymentService.setPaymentMethods(methods);
                    fullScreenLoader.stopLoader();
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    fullScreenLoader.stopLoader();
                }
            );
        };

        return defaultProcessor;
    };
});

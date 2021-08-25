/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/model/full-screen-loader',
    'creditcard-init-payment',
    'creditcard-3dsv2-validation',
    'jquery'
], function (
    Component,
    redirectOnSuccessAction,
    loader,
    initCCPaymentAction,
    cc3DSv2Validation,
    $
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'CM_Payments/payment/creditcard',
            encryptedData: null,
            cardHolder: null,
            cardNumber: null,
            cvv: null,
            selectedMonth: null,
            selectedYear: null,
            paymentConfig: '',
            placeOrderHandler: null
        },

        /**
         * Init observable
         *
         * @returns {*}
         */
        initObservable: function () {
            this._super();
            this.paymentConfig = window.checkoutConfig.payment[this.item.method];

            this.loadEncryptionLibrary(function () {
                console.log(window.cseEncrypt);
                console.log('loaded');
            })

            this.loadNsa3DsLibrary(function () {
                console.log(window.nca3DSWebSDK);
                console.log('loaded');
            })

            return this;
        },

        /**
         * @param {Function} handler
         */
        setPlaceOrderHandler: function (handler) {
            this.placeOrderHandler = handler;
        },

        /**
         * Get payment method form
         *
         * @returns {*|define.amd.jQuery|HTMLElement}
         */
        getForm: function () {
            return $('#' + this.item.method + '-form');
        },

        /**
         * Get the gateway image
         *
         * @returns {String}
         */
        getImage: function () {
            return this.paymentConfig.image;
        },

        /**
         * Add extra data to request payload paymentInformation
         *
         * @returns {{additional_data: {iban: *}, method}}
         */
        getData: function () {
            return {
                'method': this.item.method,
                'additional_data': {
                    "data": this.data
                }
            };
        },

        /**
         * Load Encryption library
         *
         * @param callback
         */
        loadEncryptionLibrary: function (callback) {
            $.getScript(this.paymentConfig.encryption_library, callback);
        },

        /**
         * Load Nsa3Ds library
         *
         * @param callback
         */
        loadNsa3DsLibrary: function (callback) {
            $.getScript(this.paymentConfig.nsa3ds_library, callback);
        },

        /**
         *
         * @return {boolean|*}
         */
        encryptCreditCardFields: function () {
            if (typeof window.cseEncrypt === 'undefined') {
                console.error('CM.com encryption library is not loaded');
                return false;
            }

            return window.cseEncrypt(
                this.getCardHolder(),
                this.getCardNumber(),
                this.getSelectedMonth(),
                this.getSelectedYear(),
                this.getCvv()
            );
        },

        /**
         * Checks if creditcard mode is set to 'direct'
         *
         * @returns {boolean}
         */
        isDirect: function () {
            return this.paymentConfig.is_direct;
        },

        /**
         * Get array of months
         *
         * @returns {string[]}
         */
        getMonths: function () {
            return ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        },

        /**
         * Get array of years
         *
         * @returns {string[]}
         */
        getYears: function () {
            let currentYear = new Date().getFullYear().toString().substr(-2), years = [],
                endYear = currentYear + 20;
            while ( currentYear <= endYear ) {
                years.push(currentYear++);
            }

            return years;
        },

        /**
         * Get Card Holder
         *
         * @returns {String}
         */
        getCardHolder: function () {
            return this.cardHolder;
        },

        /**
         * Get Card Number
         *
         * @returns {String}
         */
        getCardNumber: function () {
            return this.cardNumber;
        },

        /**
         * Get Cvv
         *
         * @returns {String}
         */
        getCvv: function () {
            return this.cvv;
        },

        /**
         * Get selected year
         *
         * @returns {String}
         */
        getSelectedYear: function () {
            return this.selectedYear;
        },

        /**
         * Get selected month
         *
         * @returns {String}
         */
        getSelectedMonth: function () {
            return this.selectedMonth;
        },

        /**
         * Get encrypted credit card data
         *
         * @returns {Object}
         */
        getEncryptedCreditCardData: function () {
            return {
                "data": this.encryptCreditCardFields()
            };
        },

        /**
         * Validate form
         *
         * @returns {*}
         */
        validate: function () {
            let $form = this.getForm().validation()
            this.data = this.encryptCreditCardFields()

            return this.data && $form.validation() && $form.validation('isValid');
        },

        /**
         * Place order function
         *
         * @return {boolean}
         */
        placeOrder: function () {
            let self = this;
            if (this.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {
                loader.startLoader();
                this.isPlaceOrderActionAllowed(false);
                $.when(
                    initCCPaymentAction(this.messageContainer, self.getData())
                ).done(
                    function (response) {
                        if (response) {
                            if (cc3DSv2Validation.perform3DsSteps(response, self.messageContainer)) {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        }
                    }
                ).always(
                    function () {
                        self.isPlaceOrderActionAllowed(true);
                        loader.stopLoader();
                    }
                );
            }

            return false;
        },

        /**
         * Redirect to controller for payment confirmation after place order
         */
        afterPlaceOrder: function () {
            this.redirectAfterPlaceOrder = false;
        }
    });
});

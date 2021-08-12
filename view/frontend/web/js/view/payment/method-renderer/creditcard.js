/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/redirect-on-success',
    'mage/url',
    'jquery'
], function (
    Component,
    redirectOnSuccessAction,
    url,
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
            paymentConfig: ''
        },

        /**
         * Init observable
         *
         * @returns {*}
         */
        initObservable: function () {
            this._super();
            this.paymentConfig = window.checkoutConfig.payment[this.item.method];

            this.loadEncryptionLibrary(function() {
                console.log(window.cseEncrypt);
                console.log('loaded');
            })

            return this;
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
         * @returns {string}
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

        loadEncryptionLibrary: function(callback) {
            $.getScript(this.paymentConfig.encryption_library, callback);
        },

        encryptCreditCardFields: function() {
            if(window.cseEncrypt === undefined) {
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
         * @returns {boolean}
         */
        isDirect: function () {
            return this.paymentConfig.is_direct;
        },

        /**
         * Get array of months
         * @returns {string[]}
         */
        getMonths: function() {
            return ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        },

        /**
         * Get array of years
         * @returns {string[]}
         */
        getYears: function() {
            var currentYear = new Date().getFullYear().toString().substr(-2), years = [];
            var endYear = currentYear + 20;
            while ( currentYear <= endYear ) {
                years.push(currentYear++);
            }
            return years;
        },

        /**
         *
         * @returns {null}
         */
        getCardHolder: function() {
            return this.cardHolder;
        },

        getCardNumber: function() {
            return this.cardNumber;
        },

        getCvv: function() {
            return this.cvv;
        },

        /**
         * Get selected year
         *
         * @returns string
         */
        getSelectedYear: function () {
            return this.selectedYear;
        },

        /**
         * Get selected month
         *
         * @returns string
         */
        getSelectedMonth: function () {
            return this.selectedMonth;
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

        placeOrder: function() {
            // Todo: create cm order + cm payment and validate 3d secure.

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

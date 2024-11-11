/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/modal/alert'
], function (
    Component,
    $,
    alert
) {
    'use strict';

    /**
     * @type {string}
     */
    const ENCRYPTED_VALUE_PLACEHOLDER = '******'

    return Component.extend({
        checkApiConnectionUrl: null,
        resultContainerSelector: null,
        modeContainerSelector: null,
        mode: null,
        merchantNameContainer: null,
        merchantPasswordContainer: null,
        merchantKeyContainer: null,

        /**
         * Initializes component
         */
        initialize: function () {
            this._super();

            this.mode = $(this.modeContainerSelector).val();
            this.merchantNameContainer = $('#cm_payments_general_api_details_' + this.mode + '_merchant_name');
            this.merchantPasswordContainer = $('#cm_payments_general_api_details_' + this.mode + '_merchant_password');
            this.merchantKeyContainer = $('#cm_payments_general_api_details_' + this.mode + '_merchant_key');

            this.merchantPasswordContainer.focus(this.clearEncryptedPlaceholder);
            this.merchantKeyContainer.focus(this.clearEncryptedPlaceholder);

            this.merchantPasswordContainer.blur(this.recoverEncryptedPlaceholder);
            this.merchantKeyContainer.blur(this.recoverEncryptedPlaceholder);

            return this;
        },

        /**
         * Clearing of Encrypted Placeholder
         */
        clearEncryptedPlaceholder: function () {
            if ($(this).val() == ENCRYPTED_VALUE_PLACEHOLDER) {
                $(this).val('');
            }
        },

        /**
         * Recovering of Encrypted Placeholder
         */
        recoverEncryptedPlaceholder: function () {
            if (!$(this).val()) {
                $(this).val(ENCRYPTED_VALUE_PLACEHOLDER);
            }
        },

        /**
         * Check API Connection function
         */
        checkApiConnection: function () {
            let result = $(this.resultContainerSelector),
                resultHtml = '',
                merchantName = this.merchantNameContainer.val(),
                merchantPassword = this.merchantPasswordContainer.val(),
                merchantKey = this.merchantKeyContainer.val();

            if (merchantName !== '' && merchantPassword !== '' && merchantKey !== '') {
                $.ajax({
                    showLoader: true,
                    url: this.checkApiConnectionUrl,
                    data: {
                        'form_key': $('[name="form_key"]').val(),
                        'merchantData': {
                            mode: this.mode,
                            merchantName: merchantName,
                            merchantPassword: merchantPassword,
                            merchantKey: merchantKey,
                        }
                    },
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        result.hide();
                    },
                    error: function (response) {
                        if (response.status > 200) {
                            alert({
                                title: $.mage.__('Warning'),
                                content: response.statusText,
                                actions: {}
                            });
                        }
                    }
                }).done(function (response) {
                    if (response.status > 200) {
                        alert({
                            title: $.mage.__('Warning'),
                            content: response.statusText,
                            actions: {}
                        });
                    } else {
                        let resultData = response.result || response;

                        if (resultData.connectionResult) {
                            if (resultData.success) {
                                resultHtml = '<strong class="cmpayments-api-connection-success">'
                                    + resultData.connectionResult
                                    + '</strong>';
                            } else {
                                resultHtml = '<strong class="cmpayments-api-connection-error">'
                                    + resultData.connectionResult
                                    + '</strong>';
                            }
                        } else {
                            let translatedResult = $.mage.__('The connection was not finished successfully!');

                            resultHtml = '<strong class="cmpayments-api-connection-error">'
                                + translatedResult
                                + '</strong>';
                        }

                        result.fadeIn();
                        result.html(resultHtml);
                    }
                });
            } else {
                let translatedResult = $.mage.__('Your Test Merchant data is not filled properly. Please, check.');
                if (this.mode == 'live') {
                    translatedResult = $.mage.__('Your Production Merchant data is not filled properly. Please check.');
                }

                resultHtml = '<strong class="cmpayments-api-connection-error">'
                    + translatedResult
                    + '</strong>';

                result.fadeIn();
                result.html(resultHtml);
            }
        }
    });
})

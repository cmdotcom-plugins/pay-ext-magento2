/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'loader'
], function (
    Component,
    $,
    alert
) {
    return Component.extend({
        wrapperContainerSelector: null,
        modeContainerSelector: null,
        checkApiConnectionUrl: null,
        resultContainerSelector: null,

        checkApiConnection: function () {
            let mode = $(this.modeContainerSelector).val(),
                merchant_name = $('#cm_payments_general_api_details_' + mode + '_merchant_name').val(),
                merchant_password =  $('#cm_payments_general_api_details_' + mode + '_merchant_password').val(),
                merchant_key = $('#cm_payments_general_api_details_' + mode + '_merchant_key').val(),
                container = $(this.wrapperContainerSelector),
                result = $(this.resultContainerSelector),
                resultHtml = '';

            if (merchant_name !== '' && merchant_password !== '' && merchant_key !== '') {
                container.loader({texts: ''});
                $.ajax({
                    showLoader: false,
                    url: this.checkApiConnectionUrl,
                    data: {
                        'form_key': $('[name="form_key"]').val(),
                        'mode': mode,
                        'merchant_name': merchant_name,
                        'merchant_password': merchant_password,
                        'merchant_key': merchant_key
                    },
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        container.loader('show');
                        result.hide();
                    },
                    complete: function () {
                        container.loader('hide');
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
                            let translatedResult = $.mage.__('The connection to Test account was not finished' +
                                ' successfully!');
                            if (mode == 'live') {
                                translatedResult = $.mage.__('The connection to Live account was not finished' +
                                    ' successfully!');
                            }

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
                if (mode == 'live') {
                    translatedResult = $.mage.__('Your Live Merchant data is not filled properly. Please, check.');
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

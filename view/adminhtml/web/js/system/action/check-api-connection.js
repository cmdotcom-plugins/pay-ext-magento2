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

    return Component.extend({
        wrapperContainerSelector: null,
        checkApiConnectionUrl: null,
        resultContainerSelector: null,

        /**
         * Check API Connection function
         */
        checkApiConnection: function () {
            let result = $(this.resultContainerSelector),
                resultHtml = '';

            $.ajax({
                showLoader: true,
                url: this.checkApiConnectionUrl,
                data: {
                    'form_key': $('[name="form_key"]').val()
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
        }
    });
})

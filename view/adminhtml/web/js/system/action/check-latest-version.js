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
    'use strict';

    return Component.extend({
        wrapperContainerSelector: null,
        checkLatestVersionUrl: null,
        resultContainerSelector: null,

        checkLatestVersion: function () {
            let container = $(this.wrapperContainerSelector),
                result = $(this.resultContainerSelector),
                resultHtml = '';

            container.loader({texts: ''});
            $.ajax({
                showLoader: false,
                url: this.checkLatestVersionUrl,
                data: {'form_key': $('[name="form_key"]').val()},
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
                    let resultData = response.result || response,
                        currentVersion = resultData.currentVersion.replace(/v|version/gi, ''),
                        latestVersion = resultData.latestVersion.replace(/v|version/gi, '');

                    if (!latestVersion) {
                        resultHtml = '<strong class="cmpayments-version-is-latest">'
                            + $.mage.__('There no new version available for now. Please, check later.')
                            + '</strong>';
                    } else if (currentVersion === latestVersion) {
                        resultHtml = '<strong class="cmpayments-version-is-latest">'
                            + $.mage.__('Great, you are using the latest version.')
                            + '</strong>';
                    } else {
                        let translatedResult = $.mage.__('There is a new version available <span>(%1)</span>.')
                            .replace('%1', latestVersion);

                        resultHtml = '<strong class="cmpayments-version-is-not-latest"">'
                            + translatedResult
                            + '</strong>';
                    }

                    result.fadeIn();
                    result.html(resultHtml);
                }
            });
        }
    });
});

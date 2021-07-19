/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

require([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'loader'
], function ($, alert) {
    $('.cmpayments-result-version-wrapper').loader({texts: ''});

    /**
     * Ajax request event
     */
    $('#cmpayments_button_version').click(function () {
        let container = $('.cmpayments-result-version-wrapper'),
            result = $('#cmpayments-result-version');

        $(this).fadeOut(300).addClass('cmpayments-button-disabled');
        $.ajax({
            showLoader: false,
            url: container.data('cmpayments-endpoint-url'),
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
                    resultHtml = '',
                    currentVersion = resultData.currentVersion.replace(/v|version/gi, ''),
                    latestVersion = resultData.latestVersion.replace(/v|version/gi, '');

                if (currentVersion === latestVersion) {
                    resultHtml = '<strong class="cmpayments-version">'
                        + $.mage.__('Great, you are using the latest version.')
                        + '</strong>';
                } else {
                    let translatedResult = $.mage.__('There is a new version available <span>(%1)</span>.')
                        .replace('%1', latestVersion);

                    resultHtml = '<strong class="cmpayments-version">'
                        + translatedResult
                        + '</strong>';
                }

                result.fadeIn();
                result.html(resultHtml);
            }
        });
    });
});

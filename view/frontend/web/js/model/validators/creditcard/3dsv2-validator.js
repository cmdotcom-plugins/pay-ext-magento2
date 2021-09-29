/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    $t,
    alert,
    modal,
    errorProcessor,
    loader
) {
    'use strict';

    /**
     * Purpose constants
     *
     * @type {String}
     */
    const PURPOSE_HIDDEN_IFRAME = 'HIDDEN_IFRAME';
    const PURPOSE_IFRAME = 'IFRAME';

    /**
     * Result constants
     *
     * @type {String}
     */
    const RESULT_AUTHORIZED = 'AUTHORIZED';
    const RESULT_CHALLENGE = 'CHALLENGE';
    const RESULT_SOFT_DECLINE = 'SOFT_DECLINE';
    const RESULT_ERROR = 'ERROR';
    const RESULT_CANCELED = 'CANCELED';

    return {
        threeDsPassedResult: false,

        /**
         * @return {Boolean} threeDsPassedResult
         */
        get3DsPassedResult: function () {
            return this.threeDsPassedResult;
        },

        /**
         * @param {Boolean} threeDsPassedResult
         */
        set3DsPassedResult: function (threeDsPassedResult) {
            this.threeDsPassedResult = threeDsPassedResult;
        },

        /**
         * Setup of 3D Secure challenge form popup
         */
        setupChallengePopup: function () {
            let options = {
                    title: $t('Additional Challenge Confirmation'),
                    type: 'popup',
                    responsive: true,
                    innerScroll: false,
                    modalClass: 'cc-challenge-form-modal',
                    buttons: [
                        {
                            text: $.mage.__('Cancel'),
                            class: '',
                            click: function () {
                                this.closeModal();
                            }
                        }
                    ]
                },
                modalSelector = $('#cc-challenge-form-popup');

            modalSelector.find('.cc-challenge-form-popup-content').empty()
            modal(options, modalSelector);
        },

        /**
         * Open of 3D Secure challenge form popup
         */
        openChallengePopup: function () {
            let self = this;

            $('#threeDSCReqIframe').attr('width', '490px');
            $('#threeDSCReqIframe').attr('height', '410px');
            $('#threeDSCReqIframe').contents().find('form').submit(function() {
                $('#cc-challenge-form-popup').modal("closeModal");
                self.set3DsPassedResult(true);

                return self.get3DsPassedResult();
            });

            $('#cc-challenge-form-popup').modal("openModal");
        },

        /**
         * Performing of 3D Secure Steps
         *
         * @param {Object} responseData
         * @return {Boolean}
         */
        perform3DsSteps: function (
            responseData,
            messageContainer
        ) {
            if (typeof window.nca3DSWebSDK === 'undefined') {
                console.error('CM.com NSA 3D Secure library is not loaded');
                return false;
            }

            let acsMethodData = this.findUrlWithPurpose(responseData.urls, PURPOSE_HIDDEN_IFRAME),
                authenticationData = this.findUrlWithPurpose(responseData.urls, PURPOSE_IFRAME),
                acsMethodUrl = '',
                acsThreeDSMethodData = '',
                authenticationUrl = '';

            if (acsMethodData) {
                acsMethodUrl = acsMethodData.url;
                if (typeof acsMethodData.parameters !== 'undefined') {
                    acsThreeDSMethodData = acsMethodData.parameters.threeDSMethodData;
                }
            }

            if (authenticationData) {
                authenticationUrl = authenticationData.url;
                authenticationData = {
                    'browser_info': this.getBrowserInfo()
                };
            }

            return this.perform3DsAuthentication(
                acsMethodUrl,
                acsThreeDSMethodData,
                authenticationUrl,
                authenticationData,
                messageContainer,
                false
            );
        },

        /**
         * Get Deferred Object of 3Ds Authentication
         *
         * @param {String} authenticationUrl
         * @param {Object} authenticationData
         * @param {Object} messageContainer
         * @return {Boolean}
         */
        get3DsAuthenticationDeferredObject: function (
            authenticationUrl,
            authenticationData,
            messageContainer
        ) {
            let self = this;

            loader.startLoader();
            $.when(
                this.handle3DsAuthentication(
                    authenticationUrl,
                    authenticationData,
                    messageContainer
                )
            ).done(
                function () {
                    return self.get3DsPassedResult();
                }
            ).always(
                function () {
                    loader.stopLoader();
                }
            );

            return false;
        },

        /**
         * Performing of 3D Secure Authentication
         *
         * @param {String} acsMethodUrl
         * @param {Object} acsThreeDSMethodData
         * @param {String} authenticationUrl
         * @param {Object} authenticationData
         * @param {Object} messageContainer
         * @param {Boolean} forceAuthentication
         * @return {Boolean}
         */
        perform3DsAuthentication: function (
            acsMethodUrl,
            acsThreeDSMethodData,
            authenticationUrl,
            authenticationData,
            messageContainer,
            forceAuthentication
        ) {
            let self = this;
            this.setupChallengePopup();

            if (!authenticationUrl || !authenticationData) {
                this.showError('Not all ACS Authentication parameters provided');
            }

            if (authenticationData) {
                authenticationData.forceAuthentication = forceAuthentication;
            }

            if (!acsMethodUrl || !acsThreeDSMethodData) {
                // No ACS method URL, so can skip that part. Directly authenticate the shopper.
                return this.get3DsAuthenticationDeferredObject(
                    authenticationUrl,
                    authenticationData,
                    messageContainer
                );
            } else {
                if (!acsThreeDSMethodData) {
                    this.showError('Not all ACS 3DS method parameters provided');
                }

                // Create Issuer/ACS Method hidden iframe and init 3D Secure
                window.nca3DSWebSDK.createIframeAndInit3DSMethod(
                    acsMethodUrl,
                    acsThreeDSMethodData,
                    'threeDSMethodIFrame',
                    document.body,
                    function () {
                        return self.get3DsAuthenticationDeferredObject(
                            authenticationUrl,
                            authenticationData,
                            messageContainer
                        )
                    }
                );
            }
        },

        /**
         * Handling of 3D Secure Authentication response
         *
         * @param {String} authenticationUrl
         * @param {Object} authenticationData
         * @param {Object} messageContainer
         */
        handle3DsAuthentication: function (
            authenticationUrl,
            authenticationData,
            messageContainer
        ) {
            let self = this;
            return $.ajax({
                url: authenticationUrl,
                data: JSON.stringify(authenticationData),
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
            }).done(function (response) {
                if (response) {
                    switch (response.result) {
                        case RESULT_CHALLENGE:
                            let redirectUrlData = self.findUrlWithPurpose(response.urls, 'REDIRECT');
                            if (redirectUrlData) {
                                self.redirectForAuthentication(redirectUrlData.url, redirectUrlData.parameters);
                                self.set3DsPassedResult(true);
                            } else {
                                /* We have some knowledge of the returned data here. All the URLs require POST and the parameter
                                 * names are known. The parameter name is 'creq', which is the same as what is required in the form
                                 * that needs to be posted to the ACS (see also EMVco 3DSv2 specification; table A.3 - CReq/CRes POST data).
                                 */
                                let challengeUrlData = self.findUrlWithPurpose(response.urls, 'IFRAME');
                                if (challengeUrlData) {
                                    // The authentication frame is created and maintained by the OPC menu. The i-frame itself must have
                                    // a name/identifier.
                                    window.nca3DSWebSDK.createIFrameAndInit3DSChallengeRequest(
                                        challengeUrlData.url,
                                        challengeUrlData.parameters['creq'],
                                        '02',
                                        'threeDSCReqIFrame',
                                        $('#cc-challenge-form-popup').find('.cc-challenge-form-popup-content')[0],
                                        self.openChallengePopup
                                    );
                                    self.set3DsPassedResult(true);
                                } else {
                                    self.set3DsPassedResult(false);
                                    self.showError($t('Unable to finish 3D Secure Challenge Request. Please, try' +
                                        ' later.'));
                                }
                            }
                            break;
                        case RESULT_SOFT_DECLINE:
                            /* The payment authorization was soft-decline by the acquirer/issuer; the payment can be completed
                             * if the shopper is authenticated. This requires a restart of the authentication process, so the
                             * function 'performThreeDsAuthentication' is called again.
                             *
                             * We have some knowledge of the returned data here. All the URLs require POST and the parameter
                             * names are known. The ACS method is passed along under 'threeDSMethodData' and must be posted
                             * using the same (parameter) name (see also EMVco 3DSv2 specification; table A.2 - 3DS Method Data).
                             */

                            let acsMethodData = self.findUrlWithPurpose(response.urls, PURPOSE_HIDDEN_IFRAME),
                                authenticationData = self.findUrlWithPurpose(response.urls, PURPOSE_IFRAME),
                                acsMethodUrl = '',
                                acsThreeDSMethodData = '',
                                authenticationUrl = '';

                            if (acsMethodData) {
                                acsMethodUrl = acsMethodData.url;
                                acsThreeDSMethodData = acsMethodData.parameters.threeDSMethodData;
                            }

                            if (authenticationData) {
                                authenticationUrl = authenticationData.url;
                                authenticationData = {
                                    'force_authentication': false,
                                    'browser_info': self.getBrowserInfo()
                                };
                            }

                            return self.perform3DsAuthentication(
                                acsMethodUrl,
                                acsThreeDSMethodData,
                                authenticationUrl,
                                authenticationData,
                                messageContainer,
                                true
                            );

                            break;
                        case RESULT_AUTHORIZED:
                            self.set3DsPassedResult(true);
                            break;
                        case RESULT_ERROR:
                        case RESULT_CANCELED:
                            if (response.error.message) {
                                self.showError(response.error.message);
                            } else {
                                self.showError($t('Unable to finish 3D Secure Authentication. Please, try later.'));
                            }

                            self.set3DsPassedResult(false);
                            break;
                    }
                } else {
                    self.showError($t('Unable to finish 3D Secure Authentication. Please, try later.'));
                    self.set3DsPassedResult(false);
                };
            }).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    self.set3DsPassedResult(false);
                }
            );
        },

        /**
         * Finding purpose URLs in response
         *
         * @param {Object} urls
         * @param {String} purpose
         * @return {String|null}
         */
        findUrlWithPurpose: function (
            urls,
            purpose
        ) {
            for (let url of urls) {
                if (url.purpose === purpose) {
                    return url;
                }
            }

            return null;
        },

        /**
         * Showing of error
         *
         * @param {String} errorMessage
         */
        showError: function (
            errorMessage
        ) {
            alert({
                title: $t('Warning'),
                content: errorMessage,
                actions: {}
            });
        },

        /**
         * Getting of Browser Info for Authentication request
         *
         * @return {Object}
         */
        getBrowserInfo: function () {
            return {
                'java_enabled': navigator.javaEnabled(),
                'java_script_enabled': true,
                'language': navigator.language,
                'color_depth': screen.colorDepth,
                'screen_height': screen.height,
                'screen_width': screen.width,
                'time_zone_offset': new Date().getTimezoneOffset(),
                'challenge_window_size': '05'
            };
        },

        /**
         * Redirect to Authentication
         *
         * @param {String} authenticationUrl
         * @param {Object} postParameters
         */
        redirectForAuthentication: function (
            authenticationUrl,
            postParameters
        ) {
            let form = document.createElement('form');
            document.body.appendChild(form);

            form.method = 'post';
            form.action = authenticationUrl;
            for (let name in postParameters) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = postParameters[name];
                form.appendChild(input);
            }

            form.submit();
        }
    };
});

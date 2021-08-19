/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
], function (
    $,
    $t,
    alert
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
        /**
         * Performing of 3D Secure Steps
         *
         * @param {Object} responseData
         * @return {Boolean}
         */
        perform3DsSteps: function (
            responseData
        ) {
            let acsMethodData = this.findUrlWithPurpose(responseData.urls, PURPOSE_HIDDEN_IFRAME),
                authenticationData = this.findUrlWithPurpose(responseData.urls, PURPOSE_IFRAME),
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
                    'browser_info': this.getBrowserInfo()
                };
            }

            return this.perform3DsAuthentication(
                acsMethodUrl,
                acsThreeDSMethodData,
                authenticationUrl,
                authenticationData,
                false
            );
        },

        /**
         * Performing of 3D Secure Authentication
         *
         * @param {String} acsMethodUrl
         * @param {Object} acsThreeDSMethodData
         * @param {String} authenticationUrl
         * @param {Object} authenticationData
         * @param {Boolean} forceAuthentication
         * @return {Boolean}
         */
        perform3DsAuthentication: function (
            acsMethodUrl,
            acsThreeDSMethodData,
            authenticationUrl,
            authenticationData,
            forceAuthentication
        ) {
            let component = this,
                success = false;
            if (!authenticationUrl || !authenticationData) {
                this.showError('Not all ACS Authentication parameters provided');
            }

            authenticationData.forceAuthentication = forceAuthentication;
            if (!acsMethodUrl) {
                // No ACS method URL, so can skip that part. Directly authenticate the shopper.
                success = this.handle3DsAuthentication(authenticationUrl, authenticationData);
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
                        success = component.handle3DsAuthentication(
                            authenticationUrl,
                            authenticationData
                        )
                    }
                );
            }

            return success;
        },

        /**
         * Handling of 3D Secure Authentication response
         *
         * @param {String} authenticationUrl
         * @param {Object} authenticationData
         * @return {Boolean}
         */
        handle3DsAuthentication: function (
            authenticationUrl,
            authenticationData
        ) {
            let component = this,
                success = false;
            $.ajax({
                showLoader: true,
                url: authenticationUrl,
                data: JSON.stringify(authenticationData),
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
            }).done(function (response) {
                if (response) {
                    debugger;
                    switch (response.result) {
                        case RESULT_CHALLENGE:
                            let redirectUrlData = component.findUrlWithPurpose(response.urls, 'REDIRECT');
                            if (redirectUrlData) {
                                component.redirectForAuthentication(redirectUrlData.url, redirectUrlData.parameters);
                            } else {
                                /* We have some knowledge of the returned data here. All the URLs require POST and the parameter
                                 * names are known. The parameter name is 'creq', which is the same as what is required in the form
                                 * that needs to be posted to the ACS (see also EMVco 3DSv2 specification; table A.3 - CReq/CRes POST data).
                                 */
                                let challengeUrlData = component.findUrlWithPurpose(response.urls, 'IFRAME');
                                if (challengeUrlData) {
                                    // The authentication frame is created and maintained by the OPC menu. The i-frame itself must have
                                    // a name/identifier.
                                    let authenticationFrame = component.generateAuthenticationFrame('challengeFrame');
                                    window.nca3DSWebSDK.init3DSChallengeRequest(
                                        challengeUrlData.url,
                                        challengeUrlData.parameters['creq'],
                                        authenticationFrame
                                    );
                                } else {
                                    success = false;
                                    component.showError($t('Unable to finish 3D Secure Challenge Request. Please, try' +
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

                            let acsMethodData = component.findUrlWithPurpose(response.urls, PURPOSE_HIDDEN_IFRAME),
                                authenticationData = component.findUrlWithPurpose(response.urls, PURPOSE_IFRAME),
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
                                    'browser_info': component.getBrowserInfo()
                                };
                            }

                            success = component.perform3DsAuthentication(
                                acsMethodUrl,
                                acsThreeDSMethodData,
                                authenticationUrl,
                                authenticationData,
                                true
                            );

                            break;
                        case RESULT_AUTHORIZED:
                            success = true;
                            break;
                        case RESULT_ERROR:
                        case RESULT_CANCELED:
                            success = false;
                            if (response.error.message) {
                                component.showError(response.error.message);
                            } else {
                                component.showError($t('Unable to finish 3D Secure Authentication. Please, try later.'));
                            }
                            break;
                    }
                } else {
                    component.showError($t('Unable to finish 3D Secure Authentication. Please, try later.'));
                }
            }).fail(function (jqXHR, textStatus) {
                component.showError(textStatus);
            });

            return success;
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
         * @return {Object)
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
        },

        /**
         * @param {String} name
         * @return {HTMLElement}
         */
        generateAuthenticationFrame: function (name) {
            let iFrame = document.createElement('iframe');
            iFrame.name = name;

            return iFrame;
        }
    };
});

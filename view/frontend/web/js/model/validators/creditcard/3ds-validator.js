/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'ko',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    ko,
    $t,
    alert,
    modal,
    errorProcessor,
    loader
) {
    'use strict';
    /**
     * For all credit and debit cards authentication of the shopper is supported,
     * which is handled by either 3D Secure version 1 (3DSv1) or 3D Secure version 2 (3DSv2).
     * Both protocols are designed by the card schemes (Mastercard, Visa, Amex) and supported by the payment system.
     *
     * 3Dsv1
     * For 3Dsv1 authentication only a redirect has to be performed in the browser towards the issuing bank of the card.
     *
     * 3Dsv2
     * For 3DS authentication one or two URLs are returned as part of the start payment response. The two possible URLs are:
     * - The Issuer/ACS Method URL, which must be loaded in a hidden i-frame. The purpose of this URL will be set to HIDDEN_IFRAME.
     * - The authentication URL, which must be used to start the authentication with. The purpose of the URL is set to 'IFRAME',
     *   but should not be loaded into an i-frame. The response (of the POST request) must be loaded into an i-frame.
     */


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
        validation: null,
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
            const self = this;
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

            self.validation({status: RESULT_CHALLENGE, action: 'AUTHENTICATE'});
            $('#cc-challenge-form-popup').modal("openModal");

            $('#cc-challenge-form-popup').on('modalclosed', function() {
                self.validation({status: RESULT_CHALLENGE, action: 'CLOSE_MODAL'});
            });
        },

        /**
         * Performing of 3D Secure Steps
         *
         * @param {Object} responseData
         * @return {Promise<{status: boolean, type: string}>}
         */
        perform3DsSteps: function (
            responseData
        ) {
            this.validation = ko.observable();

            if (typeof window.nca3DSWebSDK === 'undefined') {
                console.error('CM.com NSA 3D Secure library is not loaded');
                return this.validation({status: 'ERROR'});
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

            this.perform3DsAuthentication(
                acsMethodUrl,
                acsThreeDSMethodData,
                authenticationUrl,
                authenticationData,
                false
            );

            return this.validation;
        },

        /**
         * Get Deferred Object of 3Ds Authentication
         *
         * @param {String} authenticationUrl
         * @param {Object} authenticationData
         * @return {Boolean}
         */
        get3DsAuthenticationDeferredObject: function (
            authenticationUrl,
            authenticationData
        ) {
            loader.startLoader();
            $.when(
                this.handle3DsAuthentication(
                    authenticationUrl,
                    authenticationData
                )
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
            let self = this;
            this.setupChallengePopup();

            if (!authenticationUrl || !authenticationData) {
                console.error('Not all ACS Authentication parameters provided');
                return this.validation({status: 'ERROR'});
            }

            if (authenticationData) {
                authenticationData.forceAuthentication = forceAuthentication;
            }

            if (!acsMethodUrl || !acsThreeDSMethodData) {
                // No ACS method URL, so can skip that part. Directly authenticate the shopper.
                return this.get3DsAuthenticationDeferredObject(
                    authenticationUrl,
                    authenticationData
                );
            } else {
                if (!acsThreeDSMethodData) {
                    console.error('Not all ACS 3DS method parameters provided');
                    return this.validation({status: 'ERROR'});
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
                            authenticationData
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
         */
        handle3DsAuthentication: function (
            authenticationUrl,
            authenticationData
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
                                self.validation({status: RESULT_CHALLENGE, action: 'REDIRECT'});
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
                                        self.openChallengePopup.bind(self)
                                    );
                                } else {
                                    console.error($t('Unable to finish 3D Secure Challenge Request. Please, try later.'));
                                    self.validation({status: 'ERROR'});
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
                                true
                            );

                            break;
                        case RESULT_AUTHORIZED:
                            self.validation({status: RESULT_AUTHORIZED});
                            break;
                        case RESULT_ERROR:
                        case RESULT_CANCELED:
                            self.validation({status: response.result});
                            break;
                    }
                } else {
                    self.validation({status: 'canceled'});
                };
            }).fail(
                function (response) {
                    self.validation({status: 'ERROR'});
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
         * 3DSv1
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

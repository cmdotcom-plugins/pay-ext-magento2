/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/additional-validators',
    'CM_Payments/js/model/elv/iban-agreements-validator'
], function (
    Component,
    additionalValidators,
    elvIbanAgreementsValidator
) {
    'use strict';

    additionalValidators.registerValidator(elvIbanAgreementsValidator);

    return Component.extend({});
});

/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'uiComponent',
    'jquery'
], function (
    Component,
    $
) {
    'use strict';

    return Component.extend({
        mainContainerSelector: null,

        /**
         * Initialization of component
         *
         * @returns {Object}
         */
        initialize: function () {
            this._super();
            let self = this;
            $(this.mainContainerSelector).find('select').each(function() {
                if ($(this).attr('id').indexOf('allowspecific') !== -1) {
                    self.initChildValues($(this));
                }

                if ($(this).attr('id').indexOf('allow_specific_currency') !== -1) {
                    self.initChildValues($(this));
                    $(this).change(function (event) {
                        self.initChildValues($(event.target));
                    });
                }
            })

            return this;
        },

        /**
         * Init of child values (specific countries, currencies)
         *
         * @param {HTMLElement} element
         */
        initChildValues: function (element) {
            let childValuesContainer = element.closest('tr').nextAll('tr')[0];
            if (element.val() == '0') {
                $(childValuesContainer).find('select').attr('disabled', true);
            } else {
                $(childValuesContainer).find('select').attr('disabled', false);
            }
        }
    });
})

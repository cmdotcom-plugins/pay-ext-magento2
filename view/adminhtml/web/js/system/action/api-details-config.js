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
        modeContainerSelector: null,

        /**
         * Initialization of component
         *
         * @returns {Object}
         */
        initialize: function () {
            this._super();
            $(this.modeContainerSelector).change(this.changeApiDetailsMode);
            $(this.modeContainerSelector).trigger('change');

            return this;
        },

        /**
         * Changing of API Details Mode
         */
        changeApiDetailsMode: function () {
            let modeContainerId = $(this).closest('tr').attr('id'),
                parentTable = $(this).closest('table'),
                mode = $(this).val();
            parentTable.find('tr').each(function () {
                let containerId = $(this).attr('id');
                if (containerId !== modeContainerId) {
                    if (containerId.indexOf('_' + mode + '_') == -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                }
            });
        }
    });
})

/*
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([], function () {
    'use strict';

    return {

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
            const form = document.createElement('form');
            document.body.appendChild(form);

            form.method = 'post';
            form.action = authenticationUrl;
            for (let name in postParameters) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = postParameters[name];
                form.appendChild(input);
            }

            form.submit();
        }
    };
});

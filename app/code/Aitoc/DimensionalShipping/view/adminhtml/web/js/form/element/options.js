define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var selectBox = uiRegistry.get('index = ai_select_box');
            if (value == 1) {
                selectBox.show();
            } else {
                selectBox.hide();
            }

            return this._super();
        },
    });
});
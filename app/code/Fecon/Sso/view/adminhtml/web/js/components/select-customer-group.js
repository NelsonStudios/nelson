define([
    'Magento_Ui/js/form/element/ui-select'
], function (Select) {
    'use strict';
    return Select.extend({

        /**
         * Get selected element labels
         *
         * @returns {Array} array labels
         */
        getSelected: function () {
            if (typeof this.value() === 'string') {
                let clearedValue = this.value().split(',').filter(Boolean);
                this.value(clearedValue);
            }
            var selected = this.value();

            return this.cacheOptions.plain.filter(function (opt) {
                return _.isArray(selected) ?
                    _.contains(selected, opt.value) :
                selected == opt.value;//eslint-disable-line eqeqeq
            });
        }
    });
});
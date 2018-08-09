/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'mage/validation',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $, validator) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Brsw_CardConnect/payment/cardconnect'
            },
            getCode: function () {
                return 'cardconnect';
            },
            isActive: function () {
                return true;
            },
            /**
            * @return {jQuery}
            */
           validate: function () {
               var form = '#cardconnect-form';

               return $(form).validation() && $(form).validation('isValid');
           }
        });
    }
);


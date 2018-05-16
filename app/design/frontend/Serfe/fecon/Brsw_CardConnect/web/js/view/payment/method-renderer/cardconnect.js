/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form'
    ],
    function (Component) {
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
            }
        });
    }
);


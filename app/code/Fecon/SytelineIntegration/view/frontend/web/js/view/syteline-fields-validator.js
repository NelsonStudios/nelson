define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Fecon_SytelineIntegration/js/model/syteline-fields-validator'
    ],
    function (Component, additionalValidators, yourValidator) {
        'use strict';
        additionalValidators.registerValidator(yourValidator);
        return Component.extend({});
    }
);
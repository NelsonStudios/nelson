/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

define(
    [
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Payment/js/model/credit-card-validation/validator'
    ], function ($,Component) {
    'use strict';
    var paytraceConfig = window.checkoutConfig.payment.paytracevault;
    return Component.extend(
        {
        defaults: {
            template: 'Elsnertech_Paytrace/payment/paytracevault'
        },

        validate: function() {                
            var $form = $('#' + this.getCode() + '-form');
            return $form.validation() && $form.validation('isValid');
        },

        /**
         * Returns send check to info.
         *
         * @return {*}
         */
        getMailingAddress: function () {
            return window.checkoutConfig.payment.paytracevault.mailingAddress;
        },

        getCustomerSavedCards: function () {
                this.savedCards = JSON.parse(paytraceConfig.saved_cards);
                if (this.savedCards.length > 0) {
                    //this.haveSavedCards(true);
                    return this.savedCards; 
                }
        },

        getData: function () {
            var data = {
                method: this.getCode(),
                additional_data: {
                    paytrace_vault: $("input[name='paytrace-card-payment']:checked").val()
                }
            };

            return data;
        },
        /**
         * Returns payable to info.
         *
         * @return {*}
         */
        getPayableTo: function () {
            return window.checkoutConfig.payment.paytracevault.payableTo;
        }
        }
    );
    }
);
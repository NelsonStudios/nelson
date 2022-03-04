/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',     
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function ($, Component) {
        'use strict';
        var paytraceConfig = window.checkoutConfig.payment.paytrace;
        return Component.extend(
            {
            defaults: {
                template: 'Elsnertech_Paytrace/payment/paytrace',
                savedCards:null,
                haveSavedCards:false
            },

            validate: function() {                
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            context: function() {
                return this;
            },

            getCode: function() {
                return 'paytrace';
            },

            isActive: function() {
                return true;
            },

            isVaultEnabled: function () {
                return paytraceConfig.paytrace_vault;
            },

            /**
             * Get data
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_ss_issue': this.creditCardSsIssue(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'is_saved': $('input[name=vault_is_enabled]:checked').val()
                    }
                };
            },

            }
        );
    }
);
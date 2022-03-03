/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

define(
    [
        'jquery',
        'mage/translate',
        'Magento_Checkout/js/model/quote'
    ],
    function ($,$t,quote) {
        'use strict';
        return {
            validate: function () {
                if(quote.shippingMethod()){
                    if(quote.paymentMethod().method == 'paytracevault'){
                        if ($('input[name="paytrace-card-payment"]:checked').length == 0) {
                             alert('Please select saved card');
                             return false; 
                         } 
                    }
                }
                
            }
        }
    }
);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return {
        getSytelineExtraFields: function () {
            return {
                purchaseOrderNumber: $('#syteline_purchase_order_number').val(),
                orderStock: $('#syteline_order_monthly_stock').val()
            };
        }
    };
});

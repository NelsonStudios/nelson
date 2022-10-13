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
                purchaseOrderNumber: $('#syteline_purchase_order_number').val() ? $('#syteline_purchase_order_number').val() : 'N/A',
                orderStock: $('#syteline_order_monthly_stock').val(),
                companyName: $('#syteline_company_name').val(),
                serialNumber: $('#syteline_serial_number').val() ? $('#syteline_serial_number').val() : 'N/A',
                sytelineCompanyName: $('#syteline_company_name').val(),
            };
        }
    };
});

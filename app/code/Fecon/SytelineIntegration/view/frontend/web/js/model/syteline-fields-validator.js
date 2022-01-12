define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/quote',
    'Fecon_SytelineIntegration/js/model/syteline-extra-fields'
], function ($, $t, messageList, quote, sytelineExtraFieldsModel) {
        'use strict';
        return {
            validate: function () {
                var isValid = true,
                    sytelineExtraFields = sytelineExtraFieldsModel.getSytelineExtraFields();
                if (!sytelineExtraFields.sytelineCompanyName) {
                    messageList.addErrorMessage({ message: $t('Company name is a required field') });
                    $('#syteline_company_name').addClass('mage-error');
                    isValid = false;
                }
                if (quote.paymentMethod().method === "cashondelivery") {
                    if (!sytelineExtraFields.purchaseOrderNumber) {
                        messageList.addErrorMessage({ message: $t('Purchase Order Number is a required field') });
                        $('#syteline_purchase_order_number').addClass('mage-error');
                        isValid = false;
                    }
                    if (!sytelineExtraFields.orderStock) {
                        messageList.addErrorMessage({ message: $t('Please select an option for monthly stock') });
                        $('#syteline_order_monthly_stock').addClass('mage-error');
                        isValid = false;
                    }
                }

                return isValid;
            }
        }
    }
);
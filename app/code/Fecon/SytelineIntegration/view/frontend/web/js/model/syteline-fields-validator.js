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

                if(sytelineExtraFields.serialNumber && sytelineExtraFields.serialNumber.length > 30){
                    messageList.addErrorMessage({ message: $t('Machine Serial Number max length = 30') });
                    $('#syteline_serial_number').addClass('mage-error');
                    isValid = false;
                }

                if (quote.paymentMethod().method === "cashondelivery") {
                    if (!sytelineExtraFields.orderStock) {
                        messageList.addErrorMessage({ message: $t('Please select an option for monthly stock') });
                        $('#syteline_order_monthly_stock').addClass('mage-error');
                        isValid = false;
                    }
                }

                if(isValid){
                    messageList.clear();
                }

                return isValid;
            }
        }
    }
);

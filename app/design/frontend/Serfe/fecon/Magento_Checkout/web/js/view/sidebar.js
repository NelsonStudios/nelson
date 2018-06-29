define([
  'jquery',
  'Magento_Customer/js/model/authentication-popup',
  'Magento_Customer/js/customer-data',
  'Magento_Ui/js/modal/alert',
  'Magento_Ui/js/modal/confirm',
  'jquery/ui',
  'mage/decorate',
  'mage/collapsible',
  'mage/cookies'
  ], function ($, authenticationPopup, customerData, alert, confirm) {
    'use strict';

  $.widget('mage.sidebar', {
    /**
     * @param {jQuery.Event} event
     */
     events['click ' + this.options.button.remove] =  function (event) {
      event.stopPropagation();
      confirm({
        content: self.options.confirmMessage,
        actions: {
          /** @inheritdoc */
          confirm: function () {
            self._removeItem($(event.currentTarget));
          },

          /** @inheritdoc */
          always: function (e) {
            e.stopImmediatePropagation();
          }
        }
      });
      $("button.action-close").focus();
    };
  });
});
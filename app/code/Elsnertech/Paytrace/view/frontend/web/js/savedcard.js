/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

define(
  [
    'jquery',
    'Magento_Ui/js/modal/confirm'
  ],
 function ($, confirmation) {
    'use strict';
    return  {
        getCardListUrl : null,
        getRemoveCardUrl : null,
        getRemoveCard: function (url , form) {
          var self = this;
          self.isLodingData(true);
          $.ajax({
                  url: url,
                  type: 'POST',
                  dataType: 'json',
                  data: form.serialize(),
              complete: function (response) {  
                  var responce = response.responseJSON; 
                  if(responce.error === false){

                     self.getCardList();      
                  }else{
                      self.addError(responce.message);
                  }
                 self.isLodingData(false); 
              },
              error: function (xhr, status, errorThrown) {
                  console.log('Error happens. Try again.');
                   self.addError('Error happens. Try again.');
                  self.isLodingData(false); 
              }
          });
        },

        getCardList: function () {
          var self = this;
          var url = self.getCardListUrl;
          self.isLodingData(true);
          $.ajax({
                  url: url,
                  type: 'GET',
                  dataType: 'json',
              complete: function (response) {  
                  var responce = response.responseJSON; 
                  if(responce.html){
                      $('#saved_customer_volut').html(responce.html);
                  }          
                 self.isLodingData(false); 
              },
              error: function (xhr, status, errorThrown) {
                  console.log('Error happens. Try again.');
                  self.isLodingData(false); 
              }
          });
        },

        addError: function (message) {
        	$(".message.warning").addClass("error").removeClass("warning");
          $(".message.error").html(message);
          return false;
        },

        isLodingData: function (type) {
          if (type) {
            $('body').trigger('processStart');
          } else {
            $('body').trigger('processStop');
          }
        }
    }
});
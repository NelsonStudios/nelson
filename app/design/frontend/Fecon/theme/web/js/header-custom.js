define([
  "jquery"
],
function($) {
  "use strict";

  $(document).ready(function($) {
    $("body").addClass("page-ready");

    $('.magnifying-glass').on('click', function() {
      $('.toplinks').fadeOut('fast');
      $('#google_translate_element').fadeOut('fast');
      setTimeout(function(){
        $('.magnifying-glass').animate({'width': '200px'});
        $('#search').focus();
      }, 400);
    });
    $('#search').on('blur', function() {
      setTimeout(function(){
        $('.toplinks').fadeIn('slow');
        $('#google_translate_element').fadeIn('slow');
      }, 300);
      $('.magnifying-glass').animate({'width': '49px'}, 400);
    });

    /*applyFixesOnWindowsBrowsers();
    $(window).on('resize', function() {
      applyFixesOnWindowsBrowsers();
    });*/
  });
  return;
});


function applyFixesOnWindowsBrowsers() {
  /* Hack to detect Edge or Firefox under windows 10 to fix style issue */
  if ((navigator.userAgent.indexOf("Edge") !== -1 || navigator.userAgent.indexOf("Firefox") !== -1) && navigator.userAgent.indexOf("Windows") !== -1) {
    if($('body').width() > 767) {
      $('.shopping-cart').css({
        'top': '-49px'
      });
    } else {
      $('.shopping-cart').css({
        'top': '-25px'
      });
    }
  }
  /* Hack to detect windows 10 & Chrome to fix style issue */
  if (navigator.userAgent.indexOf("Chrome") !== -1 &&
      navigator.userAgent.indexOf("Edge") === -1 &&
      navigator.userAgent.indexOf("Windows") !== -1) {
    if($('body').width() > 767) {
      $('.shopping-cart').css({
        'top': '-48px'
      });
    } else {
      $('.shopping-cart').css({
        'top': '-24px'
      });
    }
  }
}

/**
 * @param index (int) index of the order item
 * @param itemID (int) order item ID
 * @param AddressID (int) customer address ID
 * Verify first occurrence of the address in the selected options.
 * This is to split it correctly for different combinations
 *
 */
function multiShippingAddressCheck(index, itemID, AddressID) {
  //Check shipping rules for duplicate same address with multiple shipping methods
  console.log( 'Index:' + index + ' / Item: ' + itemID);
}

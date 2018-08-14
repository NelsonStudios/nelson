define([
  "jquery"
], 
function($) {
  "use strict";

  $(document).ready(function($){
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
    /* Hack to detect windows 10 & Chrome to fix style issue */
    if (navigator.userAgent.indexOf("Chrome") !== -1 && 
    navigator.userAgent.indexOf("Windows") !== -1) {
      if($('body').width() > 767) {
        $('.shopping-cart').css({
          'top': '-48px'
        });
      }
    }
    /* Hack to detect Edge or Firefox under windows 10 to fix style issue */
    if ((navigator.userAgent.indexOf("Edge") !== -1 || navigator.userAgent.indexOf("Firefox")) && navigator.userAgent.indexOf("Windows") !== -1) {
      if($('body').width() > 767) {
        $('.shopping-cart').css({
          'top': '-49px'
        });
      }
    }
  });
  return;
});
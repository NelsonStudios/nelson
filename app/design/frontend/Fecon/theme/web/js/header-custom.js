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
    /* Hack to detect windows 10 & Chrome and fix style issue */
    if (navigator.userAgent.indexOf("Chrome") !== -1 && 
    navigator.userAgent.indexOf("Windows") !== -1) {
      $('.shopping-cart').css({
        'top': '-48px'
      });
    }
  });
  return;
});
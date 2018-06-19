define([
  "jquery"
], 
function($) {
  "use strict";

  $(document).ready(function($){
    $('.magnifying-glass').on('click', function() {
      $('.toplinks').fadeOut('fast');
      setTimeout(function(){
        $('.magnifying-glass').animate({'width': '200px'});
        $('#search').focus();
      }, 400);
    });

    $('#search').on('blur', function() {
      setTimeout(function(){
        $('.toplinks').fadeIn('slow');
      }, 300);
      $('.magnifying-glass').animate({'width': '49px'}, 400);
    });
  });
  return;
});
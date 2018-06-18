define([
  "jquery"
], 
function($) {
  "use strict";

  $(document).ready(function($){
    $('#search').on('focus', function() {
      setTimeout(function(){
        $('.magnifying-glass').animate({'width': '200px'});
      }, 400);
      $('.toplinks').fadeOut('slow');
    })
    $('#search').on('blur', function() {
      setTimeout(function(){
        $('.toplinks').fadeIn('slow');
      }, 300);
      $('.magnifying-glass').animate({'width': '49px'}, 400);
    })
  });
  return;
});
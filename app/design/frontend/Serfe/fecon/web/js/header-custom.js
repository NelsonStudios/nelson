define([
  "jquery"
], 
function($) {
  "use strict";

  $(document).ready(function($){
    $('#search').on('focus', function() {
      $('.magnifying-glass').animate({'width': '200px'});
    })
    $('#search').on('blur', function() {
      $('.magnifying-glass').animate({'width': '49px'});
    })
  });
  return;
});
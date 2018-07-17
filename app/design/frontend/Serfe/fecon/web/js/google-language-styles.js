define([
  "jquery"
  ], 
  function($) {
    "use strict";
    var interval = null;
    var intervalC = 0;
    // Scale based on header links width
    function scaleWidth(ms) {
      setTimeout(function() {
        var newRightPos = ($('.header.links').width() + 50);
        $('#google_translate_element').css('right', newRightPos + 'px');
      }, ((ms)? ms : 300));
    }
    /**
     * Function to check if page was translated already.
     * 
     * @param  {int} ms ms miliseconds to pass to scaleWidth
     * @return {void}
     */
    function checkPageTranslatedAndScale(ms) {
      if ($('.translated-ltr').length !== 0) {
        scaleWidth(ms);
        if(interval !== null) {
          clearInterval(interval);
        }
      }
    }
    /**
     * Set interval function
     * 
     * @param  {int} ms miliseconds to pass to scaleWidth
     * @return {int} intervalId
     */
    function translateLoaded(ms) {
      return setInterval(function() { checkPageTranslatedAndScale(ms) }, 1000);
    }
    $('document').ready(function () {
      interval = translateLoaded(3000);
      // RESTYLE THE DROPDOWN MENU
      $('#google_translate_element').on("click", function () {
        $("iframe.goog-te-menu-frame").contents().find(".goog-te-menu2-item div").click(function() {
          interval = translateLoaded();
        });
        // Change font family and color
        $("iframe.goog-te-menu-frame").contents().find(".goog-te-menu2, .goog-te-menu2-item div, .goog-te-menu2-item:link div, .goog-te-menu2-item:visited div, .goog-te-menu2-item:active div, .goog-te-menu2 *")
        .css({
          'background-color': '#201D16',
          'color': '#FFF',
          'text-transform': 'uppercase',
          'font-weight': '600',
          'font-family': 'Verb',
          'width':'100%'
        });
        // Change menu's padding
        $("iframe.goog-te-menu-frame").contents().find('.goog-te-menu2-item-selected').css ('display', 'none');

        // Change menu's padding
        $("iframe.goog-te-menu-frame").contents().find('.goog-te-menu2').css ('padding', '0px');

        // Change the padding of the languages
        $("iframe.goog-te-menu-frame").contents().find('.goog-te-menu2-item div').css('padding', '20px');

        // Change the width of the languages
        $("iframe.goog-te-menu-frame").contents().find('.goog-te-menu2-item').css('width', '100%');
        $("iframe.goog-te-menu-frame").contents().find('td').css('width', '100%');

        // Change hover effects
        $("iframe.goog-te-menu-frame").contents().find(".goog-te-menu2-item div").hover(function () {
          $(this).css('background-color', '#00AA73').find('span.text').css({'color': '#FFF', 'background-color': '#00AA73'});
        }, function () {
          $(this).css('background-color', '#201D16').find('span.text').css({'color': '#FFF', 'background-color': '#201D16'});
        });

        // Change Google's default blue border
        $("iframe.goog-te-menu-frame").contents().find('.goog-te-menu2').css('border', 'none');

        // Change the iframe.goog-te-menu-frame's box shadow
        $(".goog-te-menu-frame").css('box-shadow', '0 16px 24px 2px rgba(0, 0, 0, 0.14), 0 6px 30px 5px rgba(0, 0, 0, 0.12), 0 8px 10px -5px rgba(0, 0, 0, 0.3)');

        // Change the iframe.goog-te-menu-frame's size and position
        $(".goog-te-menu-frame").css({
          'height': '100%',
          'width': '100%',
          'top': '0px'
        });

        // Change iframe.goog-te-menu-frames's size
        $("iframe.goog-te-menu-frame").contents().find('.goog-te-menu2').css({
          'height': '100%',
          'width': '100%'
        });
      });
    });
  return;
});
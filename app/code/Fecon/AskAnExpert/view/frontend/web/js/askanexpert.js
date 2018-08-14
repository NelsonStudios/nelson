require([
  'jquery',
  'jquery/ui'
], function ($) {
  "use strict";
  /**
   * [myFunction description]
   * @return {[type]} [description]
   */
  function handle_recaptcha() {
    var exists = document.getElementById("g-recaptcha-response");
    if(exists.length !== 0) {
      var check = document.getElementById("g-recaptcha-response").value;
    }
    if(check == '' || check == null){
      document.getElementById("recaptcha").style.border = "1px solid #ea0e0e";
      return false;
    }
    else{
      document.getElementById("recaptcha").style.border = "none";
      return true;
    }
  }
});
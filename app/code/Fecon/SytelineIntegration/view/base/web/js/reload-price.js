define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";

    function main(config, element) {
        var $element = $(element);
        var AjaxUrl = config.AjaxUrl;
        var CurrentProduct = config.CurrentProduct;

        $(document).ready(function () {
            setTimeout(function () {
                $.ajax({
                    context: '.price-container',
                    url: AjaxUrl,
                    type: "POST",
                    showLoader: true,
                    data: {currentproduct: CurrentProduct},
                }).done(function (data) {
                    $('.price-container').trigger('processStart');
                    $('#ajaxresponse').appendTo('.product-info-price');
                    $('[data-role=priceBox]').remove();
                    $('#ajaxresponse').html(data.output);
                    $('.price-container').trigger('processStop');
                    return true;
                });
            }, 2000);
        });


    }
    ;
    return main;
});
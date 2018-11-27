define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";

    function main(config, element) {
        var $element = $(element);
        var AjaxUrl = config.AjaxUrl;
        var CurrentProduct = config.productId;

        $(document).ready(function () {
            $.ajax({
                url: AjaxUrl,
                type: "POST",
                data: {productId: CurrentProduct}
            }).done(function (data) {
                $('#ajaxresponse').appendTo('.product-info-price');
                $('[data-role=priceBox]').remove();
                $('#ajaxresponse').html(data.output);
                return true;
            });
        });


    }
    ;
    return main;
});
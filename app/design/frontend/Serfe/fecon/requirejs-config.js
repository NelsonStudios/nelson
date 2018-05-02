
var config = {
    map: {
        '*': {
            "Instafeed": "js/library/instafeed.min"
        }
    },
    shim: {
        jquery: {
            exports: '$'
        },
        'Smartwave_Megamenu/js/sw_megamenu': {
            deps: ['jquery']
        },
        'owl.carousel/owl.carousel.min': {
            deps: ['jquery']
        },
        'js/jquery.stellar.min': {
            deps: ['jquery']
        },
        'js/jquery.parallax.min': {
            deps: ['jquery']
        },
        'Magento_Catalog/js/jquery.zoom.min': {
            deps: ['jquery']
        }
    }
};
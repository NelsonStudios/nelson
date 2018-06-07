var config = {
    map: {
        '*': {
            "jquery.bez": "js/library/jquery.bez",
            "jquery.transform": "js/library/jquery.transform",
            "lazy": "js/library/lazy",
            "devphase.widget": "js/widget"
        }
    },
    shim: {
        'jquery.bez': {
            deps: ['jquery']
        },
        'jquery.transform': {
            deps: ['jquery']
        },
        'lazy': {
            deps: ['jquery']
        },
        'devphase.widget': {
            deps: ['jquery', 'lazy', 'jquery.transform', 'jquery.bez']
        }
    }
};
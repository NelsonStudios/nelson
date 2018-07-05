var config = {
    map: {
        '*': {
            "jquery.bez": "DevPhase_Feeds/js/library/jquery.bez",
            "jquery.transform": "DevPhase_Feeds/js/library/jquery.transform",
            "lazy": "DevPhase_Feeds/js/library/lazy",
            "devphase.widget": "DevPhase_Feeds/js/widget"
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
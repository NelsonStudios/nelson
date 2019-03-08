define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'cardconnect',
                component: 'Brsw_CardConnect/js/view/payment/method-renderer/cardconnect'
            }
        );
        return Component.extend({});
    }
);
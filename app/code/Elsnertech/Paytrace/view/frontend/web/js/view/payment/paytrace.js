/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'paytrace',
                component: 'Elsnertech_Paytrace/js/view/payment/method-renderer/paytrace'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);

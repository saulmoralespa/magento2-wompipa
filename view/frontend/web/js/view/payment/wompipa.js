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
                type: 'wompipa',
                component: 'Saulmoralespa_WompiPa/js/view/payment/method-renderer/wompipa'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
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
                type: 'ccpp_creditcard',
                component: 'Param_CcppCreditCard/js/view/payment/method-renderer/ccpp-creditcard'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);

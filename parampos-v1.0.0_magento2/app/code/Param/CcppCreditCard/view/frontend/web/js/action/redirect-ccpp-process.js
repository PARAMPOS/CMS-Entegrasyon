/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [],
    function () {
        'use strict';

        return {
            redirectUrl: window.checkoutConfig.processCcppUrl,

            /**
             * Provide redirect to ccpp gateway
             */
            execute: function (orderId) {
                this.redirectUrl = this.redirectUrl + '?order_id=' + orderId
                window.location.replace(this.redirectUrl);
            }
        };
    }
);

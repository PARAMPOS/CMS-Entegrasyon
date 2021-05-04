/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            transparent: 'Magento_Payment/js/transparent',
            'Magento_Payment/transparent': 'Magento_Payment/js/transparent'
        }
    },
    paths: {
        'payform': 'Param_CcppCreditCard/js/view/payment/method-renderer/jquery.payform.min',
    },
    shim: {
        'Param_CcppCreditCard/js/view/payment/method-renderer/ccpp-creditcard' : ['jquery'],
        'payform': {
            deps: ['jquery']
        }
    }
};
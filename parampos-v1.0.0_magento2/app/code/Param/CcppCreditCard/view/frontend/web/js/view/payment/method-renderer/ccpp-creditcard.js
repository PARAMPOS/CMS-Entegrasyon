/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Param_CcppCreditCard/js/action/redirect-ccpp-process',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/action/place-order',
        'mage/translate',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/totals',
        'mage/url',
        'Magento_Checkout/js/action/get-payment-information',
        'payform'
    ],
    function ($, Component, additionalValidators, redirectCcppProcessAction, fullScreenLoader, setPaymentInformationAction, placeOrderAction, $t, getTotalsAction, totals, urlBuilder,getPaymentInformationAction, payform) {
        'use strict';

        return Component.extend({
            initialize: function () {
                this._super();
            },

            defaults: {
                template: 'Param_CcppCreditCard/payment/ccpp-creditcard',
                timeoutMessage: $t('Sorry, but something went wrong. Please contact the seller.'),
                creditCardName: '',
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardName'
                    ]);

                return this;
            },

            placeOrderHandler: null,
            validateHandler: null,
            
            /**
             * Check if current payment has verification
             * @returns {Boolean}
             */
            hasNameOnCard: function () {
                return window.checkoutConfig.payment.ccform.use_name_on_card[this.getCode()];
            },
            
            /**
             * Check if current payment has verification
             * @returns {Boolean}
             */
            hasInstallment: function () {
                return window.checkoutConfig.payment.ccform.installment[this.getCode()];
            },

            /**
             * @param {Object} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Object} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Object}
             */
            context: function () {
                return this;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return 'ccpp_creditcard';
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            },
            /**
             * Place order.
             */
            placeOrder: function (data, event) {
                if (this.validateHandler() && additionalValidators.validate()) {
                    fullScreenLoader.startLoader();
                    this.isPlaceOrderActionAllowed(false);
                    $.when(placeOrderAction(
                        {
                            method: this.getCode(),
                            additional_data: {
                                'cc_owner': $('input#ccpp_creditcard_name_on_card').val(),
                                'cc_number': this.creditCardNumber(),
                                'cc_type': this.creditCardType(),
                                'cc_exp_year': $('select#ccpp_creditcard_expiration_yr').val(),
                                'cc_exp_month':  $('select#ccpp_creditcard_expiration').val(),
                                'cc_last_4': this.creditCardNumber().substr(-4),
                                'cc_cid' : $('input#ccpp_creditcard_cc_cid').val(),
                                'installment': $('input[name="installment\\[bank\\]\\[installment\\]"]:checked').val(),
                                'additional_data': ''
                            }
                        },
                        this.messageContainer
                    )).fail(
                        function (response) {
                            self.isPlaceOrderActionAllowed(true);
                            fullScreenLoader.stopLoader();
                        }
                    ).done(
                        function (orderId) {
                            redirectCcppProcessAction.execute(orderId);
                        }
                    );

                    this.initTimeoutHandler();
                }
            },
            reloadPayment: function() {
                fullScreenLoader.startLoader();
                var serviceUrl = urlBuilder.build('ccppcreditcard/checkout/totals');
                jQuery.ajax({
                    url: serviceUrl,
                    type: "POST",
                    data: {
                        installmentValue: $('input[name="installment\\[bank\\]\\[value\\]"]:checked').val(),
                        bankName: $('input[name="installment\\[bank\\]\\[name\\]"]:checked').val()
                    },
                    success: function(response) {
                        if (response) {
                            var deferred = jQuery.Deferred();
                            getTotalsAction([], deferred);
                            fullScreenLoader.stopLoader();
                            getPaymentInformationAction(deferred);
                            jQuery.when(deferred).done(function () {
                                totals.isLoading(false);
                            });
                            totals.isLoading(true);
                        }
                    }
                });
                return true;
            }
        });
    }
);

/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Param_CcppCreditCard/js/action/set-payment-and-update-totals',
        'knockout'
    ],
    function (Component, quote, priceUtils, totals, setPaymentAndUpdateTotalsAction, ko) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Param_CcppCreditCard/checkout/summary/installment-fee',
                title: 'Installment Fee',
                value: ko.observable(0.0),
                shouldDisplay: ko.observable(false)
            },
            initialize: function() {
                this._super();

                quote.paymentMethod.subscribe(function(newPaymentMethod) {
                    setPaymentAndUpdateTotalsAction(newPaymentMethod)
                });

                quote.totals.subscribe((function (newTotals) {
                    this.value(this.getFormattedTotalValue(newTotals));
                    this.shouldDisplay(this.isTotalDisplayed(newTotals));
                }).bind(this));
            },
            isTotalDisplayed: function(totals) {
                return this.getTotalValue(totals) > 0;
            },
            getTotalValue: function(totals) {
                if (typeof totals.total_segments === 'undefined' || !totals.total_segments instanceof Array) {
                    return 0.0;
                }

                return totals.total_segments.reduce(function (installmentFeeTotalValue, currentTotal) {
                    return currentTotal.code === 'installment_fee' ? currentTotal.value : installmentFeeTotalValue
                }, 0.0);
            },
            getFormattedTotalValue: function(totals) {
                return this.getFormattedPrice(this.getTotalValue(totals));
            }
        });
    }
);

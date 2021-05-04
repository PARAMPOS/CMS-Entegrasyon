<?php

namespace Param\CcppCreditCard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Param\CcppCreditCard\Gateway\Response\Handler\Request\Transaction;
use Param\CcppCreditCard\Helper\Config;

/**
 * Class CheckoutSubmitAllAfter
 *
 * @package Param\CcppCreditCard\Observer
 */
class CheckoutSubmitAllAfter implements ObserverInterface
{
    /**
     * @var \Param\CcppCreditCard\Gateway\Response\Handler\Request\Transaction
     */
    private $transaction;

    /**
     * CheckoutSubmitAllAfter constructor.
     *
     * @param \Param\CcppCreditCard\Gateway\Response\Handler\Request\Transaction $transaction
     */
    public function __construct(
        Transaction $transaction
    ) {
        $this->transaction = $transaction;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getPayment()->getMethod() == Config::PAYMENT_METHOD) {
            $quote = $observer->getEvent()->getQuote();
            $encCardData = $quote->getPayment()->getData('additional_data');
            $this->transaction->handle($order, $encCardData);
        }
    }
}

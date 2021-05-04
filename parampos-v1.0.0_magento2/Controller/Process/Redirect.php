<?php

namespace Param\CcppCreditCard\Controller\Process;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Param\CcppCreditCard\Gateway\Response\Handler\Request\Transaction;
use Param\CcppCreditCard\Helper\Config;

/**
 * Class Redirect
 *
 * @package Param\CcppCreditCard\Controller\Process
 */
class Redirect extends Action
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Param\CcppCreditCard\Gateway\Response\Handler\Request\Transaction
     */
    private $transaction;

    /**
     * Redirect constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Param\CcppCreditCard\Gateway\Response\Handler\Request\Transaction $transaction
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory,
        Transaction $transaction
    ) {
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->transaction = $transaction;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderFactory->create()->load($orderId);
        if ($order->getStatus() == Order::STATE_PENDING_PAYMENT
            && $order->getPayment()->getMethod() == Config::PAYMENT_METHOD) {
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteFactory->create()->load($quoteId);
            $payment = $quote->getPayment();
           
            $response = $this->transaction->handle($order, $payment);
            $this->getResponse()->setBody($response);
        }
    }
}

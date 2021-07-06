<?php

namespace Param\CcppCreditCard\Gateway\Response\Handler\Capture;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResource;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;

/**
 * Class CaptureTransaction
 *
 * @package Param\CcppCreditCard\Gateway\Response\Handler\Capture
 */
class CaptureTransaction implements HandlerInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var PaymentResource
     */
    protected $paymentResource;

    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CaptureTransaction constructor.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment $paymentResource
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\ObjectManagerInterface $_objectManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        PaymentResource $paymentResource,
        BuilderInterface $transactionBuilder,
        OrderFactory $orderFactory,
        InvoiceService $invoiceService,
        ObjectManagerInterface $_objectManager,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->transactionBuilder = $transactionBuilder;
        $this->paymentResource = $paymentResource;
        $this->invoiceService = $invoiceService;
        $this->_objectManager = $_objectManager;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        try {
            $payment = $handlingSubject['payment']->getPayment();
            $order = $payment->getOrder();
            /** @var $order \Magento\Sales\Model\Order */
            $formattedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($order->getPayment()->getCcTransId())
                ->setFailSafe(true)
                ->build(Transaction::TYPE_CAPTURE);

            $payment->setAmountAuthorized($order->getGrandTotal());
            $payment->setAdditionalInformation('cc_number', $order->getPayment()->getCcNumberEnc());
            $payment->setAdditionalInformation('tranRef', $order->getPayment()->getCcTransId());
            $payment->setAdditionalInformation('tranRes', $order->getPayment()->getData('tranRes'));
            $payment->setAdditionalInformation('tranDate', $order->getPayment()->getData('tranDate'));
            $payment->setAdditionalInformation('tranAmount', $order->getPayment()->getData('tranAmount'));
            $payment->setAdditionalInformation('docId', $order->getPayment()->getData('docId'));
            $payment->setAdditionalInformation('tranResponseCode', $order->getPayment()->getData('tranResponseCode'));
            $payment->addTransactionCommentsToOrder($transaction, __('The Payment Capture success. Captured amount is %1.', $formattedPrice));
            $payment->setParentTransactionId(null);
            
            $this->paymentResource->save($payment);
            $transaction->save();
            if ($order->canInvoice() === true) {
                $invoice = $this->invoiceService->prepareInvoice($order);

                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->save();

                $transactionSave = $this->_objectManager->create(
                    'Magento\Framework\DB\Transaction'
                )->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();
                $order->addCommentToStatusHistory(
                    __(
                        'Notified customer about invoice #%1.',
                        $invoice->getIncrementId()
                    )
                )->save();
            }
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
        }
    }
}
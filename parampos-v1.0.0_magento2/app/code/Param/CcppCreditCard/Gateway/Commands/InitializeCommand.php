<?php

namespace Param\CcppCreditCard\Gateway\Commands;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 * Class InitializeCommand
 *
 * @package Param\CcppCreditCard\Gateway\Commands
 */
class InitializeCommand implements CommandInterface
{
    /**
     * @param array $commandSubject
     */
    public function execute(array $commandSubject)
    {
        $stateObject = SubjectReader::readStateObject($commandSubject);
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setAmountAuthorized($payment->getOrder()->getTotalDue());
        $payment->setBaseAmountAuthorized($payment->getOrder()->getBaseTotalDue());

        $stateObject->setData(
            OrderInterface::STATE,
            Order::STATE_PENDING_PAYMENT
        );
        $stateObject->setData(
            OrderInterface::STATUS, Order::STATE_PENDING_PAYMENT
        );
        $stateObject->setData('is_notified', false);
    }
}
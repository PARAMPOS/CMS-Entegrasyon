<?php

namespace Param\CcppCreditCard\Observer;

use Exception;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Param\CcppCreditCard\Helper\Config;

/**
 * Class SalesPaymentImportData
 *
 * @package Param\CcppCreditCard\Observer
 */
class SalesPaymentImportData implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $input = $observer->getEvent()->getInput();
        if ($input->getMethod() == Config::PAYMENT_METHOD) {
            try {
                $additionalData = $input->getAdditionalData();
                if(count($additionalData)){
                    unset($additionalData['additional_data']);
                    if(!isset($additionalData['installment'])) {
                        $additionalData['installment'] = 1;
                    }
                    $ccData = [$additionalData['cc_number'], $additionalData['cc_cid'], $additionalData['installment']];
                    $additionalData['additional_data'] = implode(',',$ccData);
                    $payment->addData($additionalData)->save();
                }
            } catch (Exception $e) {
                throw new CouldNotSaveException($e->getMessage());
            }
        }
    }
}

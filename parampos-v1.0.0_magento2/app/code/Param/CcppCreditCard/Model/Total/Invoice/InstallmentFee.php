<?php
declare(strict_types = 1);

namespace Param\CcppCreditCard\Model\Total\Invoice;

class InstallmentFee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @inheritdoc
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);

        $codFee = $invoice->getOrder()->getExtensionAttributes()->getInstallmentFee();
        $baseCodFee = $invoice->getOrder()->getExtensionAttributes()->getBaseInstallmentFee();

        $invoice->setData(\Param\CcppCreditCard\Model\Total\InstallmentFee::TOTAL_CODE, $codFee);
        $invoice->setData(\Param\CcppCreditCard\Model\Total\InstallmentFee::BASE_TOTAL_CODE, $baseCodFee);

        if (round($codFee, 2) != 0)
        {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $codFee);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseCodFee);
        }

        return $this;
    }
}

<?php
declare(strict_types = 1);

namespace Param\CcppCreditCard\Model\Total\Creditmemo;

class InstallmentFee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @inheritdoc
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        parent::collect($creditmemo);

        $codFee = $creditmemo->getOrder()->getExtensionAttributes()->getInstallmentFee();
        $baseCodFee = $creditmemo->getOrder()->getExtensionAttributes()->getBaseInstallmentFee();

        $creditmemo->setData(\Param\CcppCreditCard\Model\Total\InstallmentFee::TOTAL_CODE, $codFee);
        $creditmemo->setData(\Param\CcppCreditCard\Model\Total\InstallmentFee::BASE_TOTAL_CODE, $baseCodFee);

        if (round($codFee, 2) != 0)
        {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $codFee);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseCodFee);
        }

        return $this;
    }
}

<?php
declare(strict_types = 1);
namespace Param\CcppCreditCard\Plugin\Order;

use Param\CcppCreditCard\Model\Total\InstallmentFee;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Sales\Block\Order\Totals;
use Magento\Sales\Model\Order;

class AddInstallmentFeeToTotalsBlock
{
    public function afterGetOrder(Totals $subject, Order $order): Order
    {
        if (empty($subject->getTotals())) {
            return $order;
        }

        if ($subject->getTotal(InstallmentFee::TOTAL_CODE) !== false) {
            return $order;
        }

        if (0 < ($fee = $order->getExtensionAttributes()->getInstallmentFee())) {
            $subject->addTotalBefore(new DataObject([
                'code' => InstallmentFee::TOTAL_CODE,
                'base_value' => $order->getExtensionAttributes()->getBaseInstallmentFee(),
                'value' => $fee,
                'label' => __('Installment Fee')
            ]), TotalsInterface::KEY_GRAND_TOTAL);
        }

        return $order;
    }
}

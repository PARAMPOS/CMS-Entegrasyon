<?php
declare(strict_types = 1);
namespace Param\CcppCreditCard\Plugin\Order;

use Param\CcppCreditCard\Model\Order\InstallmentFeeExtensionManagement;
use Magento\Sales\Model\Order;

class LoadInstallmentFee
{
    /**
     * @var InstallmentFeeExtensionManagement
     */
    private $extensionManagement;

    public function __construct(InstallmentFeeExtensionManagement $extensionManagement)
    {
        $this->extensionManagement = $extensionManagement;
    }

    public function afterLoad(Order $subject, Order $returnedOrder): Order
    {
        return $this->extensionManagement->setExtensionFromData($returnedOrder);
    }
}

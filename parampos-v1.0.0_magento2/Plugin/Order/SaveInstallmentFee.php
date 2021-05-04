<?php
declare(strict_types = 1);
namespace Param\CcppCreditCard\Plugin\Order;

use Param\CcppCreditCard\Model\Order\InstallmentFeeExtensionManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class SaveInstallmentFee
{
    /**
     * @var InstallmentFeeExtensionManagement
     */
    private $extensionManagement;

    public function __construct(InstallmentFeeExtensionManagement $extensionManagement)
    {
        $this->extensionManagement = $extensionManagement;
    }

    public function beforeSave(OrderRepositoryInterface $subject, Order $order): array
    {
        return [$this->extensionManagement->setDataFromExtension($order)];
    }
}
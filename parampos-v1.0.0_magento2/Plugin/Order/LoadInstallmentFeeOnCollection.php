<?php
declare(strict_types = 1);
namespace Param\CcppCreditCard\Plugin\Order;

use Param\CcppCreditCard\Model\Order\InstallmentFeeExtensionManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

class LoadInstallmentFeeOnCollection
{
    /**
     * @var InstallmentFeeExtensionManagement
     */
    private $extensionManagement;

    public function __construct(InstallmentFeeExtensionManagement $extensionManagement)
    {
        $this->extensionManagement = $extensionManagement;
    }

    public function afterGetItems(OrderCollection $subject, array $orders): array
    {
        return array_map(function (Order $order) {
            return $this->extensionManagement->setExtensionFromData($order);
        }, $orders);
    }
}

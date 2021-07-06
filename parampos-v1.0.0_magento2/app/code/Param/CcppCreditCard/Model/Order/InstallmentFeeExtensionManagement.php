<?php
declare(strict_types = 1);
namespace Param\CcppCreditCard\Model\Order;

use Param\CcppCreditCard\Model\Total\InstallmentFee;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Model\Order;

class InstallmentFeeExtensionManagement
{
    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    public function __construct(OrderExtensionFactory $orderExtensionFactory)
    {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    public function setExtensionFromData(Order $order): Order
    {
        $orderExtension = $this->getOrInitOrderExtension($order);

        $orderExtension->setInstallmentFee($order->getData(InstallmentFee::TOTAL_CODE));
        $orderExtension->setBaseInstallmentFee($order->getData(InstallmentFee::BASE_TOTAL_CODE));

        return $order;
    }

    public function setExtensionFromAddressData(Order $order, QuoteAddress $address): Order
    {
        $orderExtension = $this->getOrInitOrderExtension($order);

        $orderExtension->setInstallmentFee($address->getData(InstallmentFee::TOTAL_CODE));
        $orderExtension->setBaseInstallmentFee($address->getData(InstallmentFee::BASE_TOTAL_CODE));

        return $order;
    }

    public function setDataFromExtension(Order $order): Order
    {
        $orderExtension = $this->getOrInitOrderExtension($order);

        $order->setData(InstallmentFee::TOTAL_CODE, $orderExtension->getInstallmentFee());
        $order->setData(InstallmentFee::BASE_TOTAL_CODE, $orderExtension->getBaseInstallmentFee());

        return $order;
    }

    private function getOrInitOrderExtension(Order $order): OrderExtensionInterface
    {
        $orderExtension = $order->getExtensionAttributes();

        if ($orderExtension === null) {
            $orderExtension = $this->orderExtensionFactory->create();
            $order->setExtensionAttributes($orderExtension);

            return $orderExtension;
        }

        return $orderExtension;
    }
}

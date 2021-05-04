<?php
declare(strict_types = 1);
namespace Param\CcppCreditCard\Plugin\Quote;

use Param\CcppCreditCard\Model\Order\InstallmentFeeExtensionManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Model\Quote\Address\ToOrder as QuoteAddressToOrder;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

class InstallmentFeeToOrder
{
    /**
     * @var InstallmentFeeExtensionManagement
     */
    private $extensionManagement;

    public function __construct(InstallmentFeeExtensionManagement $extensionManagement)
    {
        $this->extensionManagement = $extensionManagement;
    }

    public function aroundConvert(
        QuoteAddressToOrder $subject,
        \Closure $proceed,
        QuoteAddress $quoteAddress,
        array $data = []
    ): OrderInterface {
        return $this->extensionManagement->setExtensionFromAddressData($proceed($quoteAddress, $data), $quoteAddress);
    }
}

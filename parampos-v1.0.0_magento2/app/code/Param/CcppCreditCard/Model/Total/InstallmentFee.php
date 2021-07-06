<?php
declare(strict_types = 1);
namespace Param\CcppCreditCard\Model\Total;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Session;
use Param\CcppCreditCard\Helper\Config;

class InstallmentFee extends AbstractTotal
{
    const TOTAL_CODE = 'installment_fee';
    const BASE_TOTAL_CODE = 'base_installment_fee';

    const LABEL = 'Installment Fee';
    const BASE_LABEL = 'Base Installment Fee';

    /**
     * @var float
     */
    public $fee = 0;
    private $baseCurrency;
    private $_checkoutSession;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        Session $_checkoutSession
    )
    {
        $this->_checkoutSession = $_checkoutSession;
        $currencyCode = $scopeConfig->getValue("currency/options/base", ScopeInterface::SCOPE_WEBSITES);
        $this->baseCurrency =  $currencyFactory->create()->load($currencyCode);
        if($this->_checkoutSession->getInstallmentFee()) {
            $this->fee = $this->_checkoutSession->getInstallmentFee();
        }
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface
        $shippingAssignment,
        Total $total
    ): InstallmentFee {     
        parent::collect($quote, $shippingAssignment, $total);
        
        if (count($shippingAssignment->getItems()) == 0) {
            return $this;
        }

        $baseInstallmentFee = $this->getFee($quote);
        $currency = $quote->getStore()->getCurrentCurrency();
        $installmentFee = $this->baseCurrency->convert($baseInstallmentFee, $currency);

        $total->setData(static::TOTAL_CODE, $installmentFee);
        $total->setData(static::BASE_TOTAL_CODE, $baseInstallmentFee);

        $total->setTotalAmount(static::TOTAL_CODE, $installmentFee);
        $total->setBaseTotalAmount(static::TOTAL_CODE, $baseInstallmentFee);

        return $this;
    }

    public function fetch(Quote $quote, Total $total): array
    {
        $base_value = $this->getFee($quote);
        if ($base_value) {
            $currency = $quote->getStore()->getCurrentCurrency();
            $value = $this->baseCurrency->convert($base_value, $currency);
        } else {
            $value = null;
        }
        return [
            'code' => static::TOTAL_CODE,
            'title' => static::LABEL,
            'base_value' => $base_value,
            'value' => $value
        ];
    }

    public function getLabel(): Phrase
    {
        return __(static::LABEL);
    }

    private function getFee(Quote $quote): float
    {
        if ($quote->getPayment()->getMethod() !== Config::PAYMENT_METHOD) {
            return (float)null;
        }
        if($quote->getInstallmentFee()){
            return $quote->getInstallmentFee();
        }
        return (float)$this->fee;
    }

    public function getCheckoutSession() 
    {
        return $this->_checkoutSession;
    }
}

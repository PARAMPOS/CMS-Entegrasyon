<?php

namespace Param\CcppCreditCard\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Param\CcppCreditCard\Model\Total\InstallmentFee;

class Totals extends \Magento\Framework\App\Action\Action 
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJson;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_helper;

    /**
     * @var \InstallmentFee
     */
    protected $installmentFee;

    /**
     * @var \PriceCurrencyInterface
     */
    protected $priceCurrency;

    public function __construct(
        Context $context,
        InstallmentFee $installmentFee,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson
    )
    {
        parent::__construct($context);
        $this->installmentFee = $installmentFee;
        $this->priceCurrency = ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_resultJson = $resultJson;
    }

    /**
     * Trigger to re-calculate the collect Totals
     *
     * @return bool
     */
    public function execute()
    {
        $response = [
            'errors' => false,
            'message' => 'Re-calculate successful.'
        ];

        $installmentData = $this->getRequest()->getPost();
        $quote = $this->_checkoutSession->getQuote();
        try {
            if($installmentData->installmentValue && $installmentData->bankName) {
                $fee = $this->calcQuote($installmentData->installmentValue);
                $this->_checkoutSession->setInstallmentFee($fee);
                $this->_checkoutSession->setInstallmentName($installmentData->bankName);
                $this->_checkoutSession->setInstallmentQuote($installmentData->installmentValue);
                $quote->collectTotals()->save();
            } else {
                $this->_checkoutSession->unsInstallmentFee();
                $this->_checkoutSession->unsInstallmentName();
                $this->_checkoutSession->unsInstallmentQuote();
            }
            $quote->collectTotals()->save();

        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultJson = $this->_resultJson->create();
        return $resultJson->setData($response);
    }

    /**
     * Undocumented function
     *
     * @param [type] $quote
     * @return void
     */
    public function calcQuote($quoteValue){
        $quote = $this->_checkoutSession->getQuote();
        $subTotal = $quote->getSubtotal();
        $discountAmount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
        $taxAmount = $quote->getShippingAddress()->getBaseTaxAmount();
        $baseGrandTotal = ($subTotal + $shippingAmount + $taxAmount) - $discountAmount;
        return $baseGrandTotal / 100 * floatval($quoteValue);
    }
}
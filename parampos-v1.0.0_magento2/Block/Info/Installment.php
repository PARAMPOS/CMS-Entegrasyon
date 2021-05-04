<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Param\CcppCreditCard\Block\Info;

use Magento\Framework\View\Element\Template\Context;
use Param\CcppCreditCard\Helper\Config;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Model\Session;

class Installment extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Param_CcppCreditCard::info/installment.phtml';

    /**
     * @var \Param\CcppCreditCard\Helper\Config
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientFactory
     */
    private $soapClientFactory;

    /**
     * @var Cart
     */
    private $cart;
    
    /** 
     * @var PriceCurrencyInterface 
     * $priceCurrency 
     */
    protected $priceCurrency;

    /** 
     * @var Session 
     * $priceCurrency 
     */
    protected $_checkoutSession;

    /**
     * Transaction constructor.
     *
     * @param \Param\CcppCreditCard\Helper\Config $config
     * @param \Param\CcppCreditCard\Model\Curl $curl
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Config $config,
        Cart $cart,
        LoggerInterface $logger,
        ClientFactory $soapClientFactory = null,
        PriceCurrencyInterface $priceCurrency,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->logger = $logger;
        $this->cart = $cart;
        $this->soapClientFactory = $soapClientFactory ?: ObjectManager::getInstance()->get(ClientFactory::class);
        $this->priceCurrency = $priceCurrency;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Create soap client with selected wsdl
     *
     * @param string $wsdl
     * @param bool|int $trace
     * @return \SoapClient
     */
    protected function _createSoapClient($wsdl, $trace = false)
    {
        $client = $this->soapClientFactory->create($wsdl, array(
			'trace' => 1,
			"encoding"=>"UTF-8",
			'stream_context' => stream_context_create(array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false, 
					'allow_self_signed' => true
                ))
            ))
        );
        return $client;
    }

    public function getQuote() {
        return $this->cart->getQuote();
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getInstallment(){

        $enabledInstallemnet = $this->config->getInstallment();
        if(!$enabledInstallemnet) {
            return false;
        }

        $url = $this->config->getPaymentUrl();
        
        $clientCode = $this->config->getMerchantId();
        $username = $this->config->getUsername();
        $password = $this->config->getSecretKey();
        $guid = $this->config->getGUID();

        $result = new \Magento\Framework\DataObject();
        $result->G = new \Magento\Framework\DataObject();
        $result->G->CLIENT_CODE = $clientCode;
        $result->G->CLIENT_USERNAME = $username;
        $result->G->CLIENT_PASSWORD = $password;
        $result->GUID = $guid;

        try {
            $client = $this->_createSoapClient($url);
            $response = $client->TP_Ozel_Oran_Liste($result);
            
            $result = $response->TP_Ozel_Oran_ListeResult;
            $DT_Bilgi = $result->{'DT_Bilgi'};
            $Sonuc = $result->Sonuc;
            $Sonuc_Str = $result->{'Sonuc_Str'};
            $xml = $DT_Bilgi->{'any'};
            $xmlstr =<<<XML
            <?xml version='1.0' standalone='yes'?>
            <root>
            {$xml}
            </root>
            XML;
            $xmlstr = str_replace(array("diffgr:", "msdata:"), '', $xmlstr);
            $data = simplexml_load_string($xmlstr);
            $quoteList = $data->diffgram->NewDataSet;
            return $quoteList;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $quote
     * @return void
     */
    public function calcQuote($quoteValue){
        $quote = $this->getQuote();
        $subTotal = $quote->getSubtotal();
        $discountAmount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
        $taxAmount = $quote->getShippingAddress()->getBaseTaxAmount();
        $baseGrandTotal = ($subTotal + $shippingAmount + $taxAmount) - $discountAmount;
        return $this->getFormatedPrice($baseGrandTotal + ($baseGrandTotal / 100 * floatval($quoteValue)));
    }

    /**
     * Function getFormatedPrice
     *
     * @param float $price
     *
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    public function getInstallmentBank(){
        return $this->_checkoutSession->getInstallmentName();
    }

    public function getInstallmentValue(){
        return $this->_checkoutSession->getInstallmentQuote();
    }

    public function isSelected($value, $code) {
        if($value == $this->getInstallmentValue() && $code == $this->getInstallmentBank()){
            return true;
        }
    }
}
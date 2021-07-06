<?php

namespace Param\CcppCreditCard\Gateway\Response\Handler\Request;

use Magento\Framework\App\ObjectManager;
use Param\CcppCreditCard\Model\Curl;
use Param\CcppCreditCard\Helper\Config;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Transaction
 *
 * @package Param\CcppCreditCard\Gateway\Response\Handler\Request
 */
class Transaction
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $frontendUrlBuilder;

    /**
     * Length required by 2c2p
     *
     * @var string
     */
    const MAX_AMOUNT_LENGTH = 12;

    /**
     * @var \Param\CcppCreditCard\Helper\Config
     */
    protected $config;

    /**
     * @var \Param\CcppCreditCard\Model\Curl
     */
    protected $curl;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientFactory
     */
    private $soapClientFactory;

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
        Curl $curl,
        LoggerInterface $logger,
        UrlInterface $frontendUrlBuilder,
        ClientFactory $soapClientFactory = null,
        SessionManager $sessionManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\Http $redirect
    ) {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->config = $config;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->soapClientFactory = $soapClientFactory ?: ObjectManager::getInstance()->get(ClientFactory::class);
        $this->_session = $sessionManager;
        $this->messageManager = $messageManager;
        $this->_redirect = $redirect;
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
                    'allow_self_signed' => true,
                    'crypto_method' =>  STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                ))
            ))
        );
        return $client;
    }

    /**
     * @param $order
     * @param $payment
     *
     * @return mixed
     */
    public function handle($order, $payment)
    {
        
        $url = $this->config->getPaymentUrl();
        $version = $this->config->getVersion();
        $clientCode = $this->config->getMerchantId();
        $guid = $this->config->getGUID();
        $username = $this->config->getUsername();
        $password = $this->config->getSecretKey();
        $desc = $this->config->getDescription();
        $uniqueTransactionCode = $order->getIncrementId();
        $currencyCode = $this->config->getCurrencyNo();
        $amt  = $this->getAmount($order->getGrandTotal());
        $billingAddress = $order->getBillingAddress();
        $panCountry = $billingAddress->getCountryId();
        $cardholderName = $payment->getData('cc_owner') ?? sprintf('%s %s', $billingAddress->getFirstname(), $billingAddress->getLastname());
        $gsm = $billingAddress->getTelephone();
        
        $paymentRequest = $this->getPaymentRequest(
            $clientCode,
            $guid,
            $username,
            $password,
            $uniqueTransactionCode,
            $desc,
            $amt,
            $currencyCode,
            $panCountry,
            $cardholderName,
            $gsm,
            $version,
            $payment
        );
        if ($this->config->isAllowDebug()) {
            $this->logger->info(__('Request for order id %1', $order->getId()));
            $this->logger->info($url);
            //$this->logger->info(serialize($paymentRequest));
        }
        
        try {
            $client = $this->_createSoapClient($url, 0);
            $response = $client->Pos_Odeme($paymentRequest);
            if($response->Pos_OdemeResult->Sonuc > 0){
                header("location: ".$response->Pos_OdemeResult->UCD_URL);
                exit;
            } else {   
                $this->messageManager->addErrorMessage(__($response->Pos_OdemeResult->Sonuc_Str));
                header("location: ". '/');
                exit;
            }
        } catch(\Exception $e){
            $this->messageManager->addErrorMessage(__('Connection failed.'));
        }
    }

    /**
     * Fix require length on 2c2p gateway
     *
     * @param $grandTotal
     *
     * @return string
     */
    private function getAmount($grandTotal)
    {
        return $grandTotal = number_format(round($grandTotal, 2), 2, ',', '');
    }

    /**
     * Generate Paymnet Request
     * @param $clientCode
     * @param $guid
     * @param $uniqueTransactionCode
     * @param $desc
     * @param $amt
     * @param $currencyCode
     * @param $panCountry
     * @param $cardholderName
     * @param $encCardData
     * @param $secretKey
     * @param $version
     *
     * @return string
     */
    private function getPaymentRequest(
        $clientCode,
        $guid,
        $username,
        $password,
        $uniqueTransactionCode,
        $desc,
        $amt,
        $currencyCode,
        $panCountry,
        $cardholderName,
        $gsm,
        $version,
        $payment
    ) {
        $paymentData = explode(',', $payment->getData('additional_data'));
        if(count($paymentData) == 3) {
            $result = new \Magento\Framework\DataObject();
            $debugData = [];
            $result->SanalPOS_ID = '';
            $result->G = new \Magento\Framework\DataObject();
            $result->G->CLIENT_CODE = $clientCode;
            $result->G->CLIENT_USERNAME = $username;
            $result->G->CLIENT_PASSWORD = $password;
            $result->GUID = $guid;
            $result->KK_Sahibi = $cardholderName;
            $result->KK_No = $paymentData[0];
            $result->KK_CVC = $paymentData[1];
            $result->KK_SK_Ay = $payment->getData('cc_exp_month');
            $result->KK_SK_Yil = $payment->getData('cc_exp_year');
            $result->KK_Sahibi_GSM = $gsm;
            $result->Hata_URL = $this->frontendUrlBuilder->getUrl('ccppcreditcard/process/response', ['SID' => $this->_session->getSessionId()]);
            $result->Basarili_URL = $this->frontendUrlBuilder->getUrl('ccppcreditcard/process/response', ['SID' => $this->_session->getSessionId()]);
            $result->Siparis_ID = $uniqueTransactionCode;
            $result->Siparis_Aciklama = date("d-m-Y H:i:s") . " tarihindeki ödeme";
            $result->Taksit = $paymentData[2];
            $result->Islem_Tutar = $amt;
            $result->Toplam_Tutar = $amt;
            $result->Islem_Hash = '';
            $result->Islem_Guvenlik_Tip = '3D';
            $result->Islem_ID = str_replace(".","",microtime(true)).rand(000,999);
            $result->IPAdr = $_SERVER['REMOTE_ADDR'];
            $result->Ref_URL = $this->frontendUrlBuilder->getUrl('checkout/onepage');
            $result->Data1 = '';
            $result->Data2 = '';
            $result->Data3 = '';
            $result->Data4 = '';
            $result->Data5 = '';
            $result->Data6 = '';
            $result->Data7 = '';
            $result->Data8 = '';
            $result->Data9 = '';
            $result->Data10 = '';
            //Dim Islem_Guvenlik_Str$ = CLIENT_CODE & GUID & Taksit & Islem_Tutar & Toplam_Tutar & Siparis_ID & Hata_URL & Basarili_URL
            $Islem_Guvenlik_Str = $result->G->CLIENT_CODE . $result->GUID . $result->Taksit . $result->Islem_Tutar . $result->Toplam_Tutar . $result->Siparis_ID . $result->Hata_URL . $result->Basarili_URL;
            try {
                $client = $this->_createSoapClient($this->config->getPaymentUrl(), 0);
                $response = $client->SHA2B64(array('Data' => $Islem_Guvenlik_Str));
                $result->Islem_Hash = $response->SHA2B64Result;
                
                return $result;
            } catch (\Exception $e) {
                throw new FrameworkException($e->getMessage());
            }
        }
    }
}
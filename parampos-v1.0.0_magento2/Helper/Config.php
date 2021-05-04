<?php

namespace Param\CcppCreditCard\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\Module\Dir;
/**
 * Class Config
 *
 * @package Param\CcppCreditCard\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @var string
     */
    const PAYMENT_METHOD = 'ccpp_creditcard';

    /**
     * @var string
     */
    const MERCHANT_ID_CONFIG_PATH = 'payment/ccpp_creditcard/merchant_id';

    /**
     * @var string
     */
    const INSTALLMENT_CONFIG_PATH = 'payment/ccpp_creditcard/installment';

    /**
     * @var string
     */
    const SECRET_KEY_CONFIG_PATH = 'payment/ccpp_creditcard/secret_key';

    /**
     * @var string
     */
    const USERNAME_KEY_CONFIG_PATH = 'payment/ccpp_creditcard/username';

    /**
     * @var string
     */
    const GUID_CONFIG_PATH = 'payment/ccpp_creditcard/guid';

    /**
     * @var string
     */
    const VERSION_CONFIG_PATH = 'payment/ccpp_creditcard/version';

    /**
     * @var string
     */
    const PAYMENT_URL_PATH = 'payment/ccpp_creditcard/payment_url';

    /**
     * @var string
     */
    const TEST_MODE_PATH = 'payment/ccpp_creditcard/testmode';

    /**
     * @var string
     */
    const TEST_PAYMENT_URL_PATH = 'payment/ccpp_creditcard/test_payment_url';

    /**
     * @var string
     */
    const CURRENCY_NO_URL_PATH = 'payment/ccpp_creditcard/currency_no';

    /**
     * @var string
     */
    const DEBUG_URL_PATH = 'payment/ccpp_creditcard/debug';

    /**
     * @var string
     */
    const DESCRIPTION_PATH = 'payment/ccpp_creditcard/description';

    /**
     * @var string
     */
    const CHECKAOUT_FAILURE_PAGE_PATH = 'payment/ccpp_creditcard/checkout_failure_page';

    /**
     * @var string
     */
    const USE_NAME_ON_CARD_PATH = 'payment/ccpp_creditcard/use_card_name';

    /**
     * @var
     */
    private $_paramPOSApi;

    /**
     * @var Dir
     */
    private $moduleDir;

    public function __construct(
        Context $context, 
        Dir $moduleDir
    ) {
        $this->moduleDir = $moduleDir;
        parent::__construct(
            $context
        );
        $wsdlBasePath = $this->moduleDir->getDir('Param_CcppCreditCard', Dir::MODULE_ETC_DIR) . '/wsdl/';
        $this->_paramPOSApi = $wsdlBasePath . 'ParamPOSApi.wsdl';
    }
    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->scopeConfig->getValue(self::MERCHANT_ID_CONFIG_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->scopeConfig->getValue(self::SECRET_KEY_CONFIG_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getInstallment()
    {
        return $this->scopeConfig->getValue(self::INSTALLMENT_CONFIG_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->scopeConfig->getValue(self::USERNAME_KEY_CONFIG_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getGUID()
    {
        return $this->scopeConfig->getValue(self::GUID_CONFIG_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->scopeConfig->getValue(self::VERSION_CONFIG_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getPaymentUrl()
    {
        if ($this->getTestMode()) {
            return $this->scopeConfig->getValue(self::TEST_PAYMENT_URL_PATH, ScopeInterface::SCOPE_WEBSITE);
        }

        if(file_exists($this->_paramPOSApi)){
            return $this->_paramPOSApi;
        }

        return $this->scopeConfig->getValue(self::PAYMENT_URL_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getTestMode()
    {
        return $this->scopeConfig->getValue(self::TEST_MODE_PATH, ScopeInterface::SCOPE_WEBSITE);

    }

    /**
     * @return string
     */
    public function getCurrencyNo()
    {
        return $this->scopeConfig->getValue(self::CURRENCY_NO_URL_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function isAllowDebug()
    {
        return $this->scopeConfig->getValue(self::DEBUG_URL_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->scopeConfig->getValue(self::DESCRIPTION_PATH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getCheckoutFailurePage()
    {
        return $this->scopeConfig->getValue(self::CHECKAOUT_FAILURE_PAGE_PATH);
    }

    /**
     * @return string
     */
    public function isUseNameOnCard()
    {
        return $this->scopeConfig->getValue(self::USE_NAME_ON_CARD_PATH, ScopeInterface::SCOPE_WEBSITE);
    }
}

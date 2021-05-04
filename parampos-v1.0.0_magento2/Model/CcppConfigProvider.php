<?php

namespace Param\CcppCreditCard\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Param\CcppCreditCard\Helper\Config;
use Magento\Framework\View\LayoutInterface;
/**
 * Class CcppConfigProvider
 *
 * @package Param\CcppCreditCard\Model
 */
class CcppConfigProvider implements ConfigProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Param\CcppCreditCard\Helper\Config
     */
    protected $config;
    
    /** 
     * @var LayoutInterface  
     */
    protected $_layout;

    /**
     * CcppConfigProvider constructor.
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Param\CcppCreditCard\Helper\Config $config
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Config $config,
        LayoutInterface $layout
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
        $this->_layout = $layout;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'processCcppUrl' => $this->urlBuilder->getUrl('ccppcreditcard/process/redirect'),
            'payment' => [
                'ccform' => [
                    'use_name_on_card' => [
                        Config::PAYMENT_METHOD => (boolean)$this->config->isUseNameOnCard()
                    ],
                    'installment' => [
                        Config::PAYMENT_METHOD => $this->_layout->createBlock('Param\CcppCreditCard\Block\Info\Installment')->toHtml()
                    ]
                ]
            ]
        ];
    }
}

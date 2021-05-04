<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Param\CcppCreditCard\Block\Payment;

/**
 * Param common payment info block
 * Uses default templates
 */
class Info extends \Magento\Payment\Block\Info\Cc
{
    /**
     * @var \Param\CcppCreditCard\Model\InfoFactory
     */
    protected $_infoFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Param\CcppCreditCard\Model\InfoFactory $infoFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Param\CcppCreditCard\Model\InfoFactory $infoFactory,
        array $data = []
    ) {
        $this->_infoFactory = $infoFactory;
        parent::__construct($context, $paymentConfig, $data);
    }

    /**
     * Prepare Param-specific payment information
     *
     * @param \Magento\Framework\DataObject|array|null $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $info = $this->_infoFactory->create();
        $info = $info->getPublicPaymentInfo($payment, true);
        return $transport->addData($info);
    }
}

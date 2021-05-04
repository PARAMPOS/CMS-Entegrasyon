<?php

namespace Param\CcppCreditCard\Controller\Process;

use Exception;
use Magento\Cms\Helper\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResource;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Param\CcppCreditCard\Helper\Config;

/**
 * Class Response
 *
 * @package Param\CcppCreditCard\Controller\Process
 */
class Response extends Action implements CsrfAwareActionInterface
{
    /**
     * @var string
     */
    const SUCCESS_STATUS = '1';

    /**
     * @var PaymentResource
     */
    protected $paymentResource;

    /**
     * @var \Param\CcppCreditCard\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $frontendUrlBuilder;

    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $pageHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Response constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Param\CcppCreditCard\Helper\Config $config
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     * @param \Magento\Cms\Helper\Page $pageHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Config $config,
        OrderFactory $orderFactory,
        UrlInterface $frontendUrlBuilder,
        Page $pageHelper,
        LoggerInterface $logger
    ) {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->config = $config;
        $this->orderFactory = $orderFactory;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->pageHelper = $pageHelper;
        $this->logger = $logger;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->paymentResource = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Payment');
        parent::__construct($context);
    }

    /** 
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request 
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Process response from 2c2p gateway
     *
     * @return mixed
     */
    public function execute()
    {
        try {
            $paymentResponse = $this->getRequest()->getPost();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $messageMessenger = $objectManager->get('Magento\Framework\Message\ManagerInterface');

            if ($paymentResponse) {
                $responseCode = (string)$paymentResponse['TURKPOS_RETVAL_Sonuc'];
                $orderId = (string)$paymentResponse['TURKPOS_RETVAL_Siparis_ID'];
                $order = $this->orderFactory->create()->load($orderId, 'increment_id');

                if ($this->config->isAllowDebug()) {
                    $this->logger->info(__('Response from Param for order id %1', $order->getId()));
                    $this->logger->info(__('Response code: %1', $responseCode));
                }

                if ($order->getId()) {
                    if ($responseCode == self::SUCCESS_STATUS) {
                        $transactionId = (string)$paymentResponse['TURKPOS_RETVAL_Islem_ID'];
                        $order->getPayment()->addData([
                            'cc_trans_id' => $transactionId, 
                            'tranRes' => $paymentResponse['TURKPOS_RETVAL_Sonuc_Str'],
                            'tranDate' => $paymentResponse['TURKPOS_RETVAL_Islem_Tarih'],
                            'tranAmount' => $paymentResponse['TURKPOS_RETVAL_Tahsilat_Tutari'],
                            'docId' => $paymentResponse['TURKPOS_RETVAL_Dekont_ID'],
                            'tranResponseCode' => $paymentResponse['TURKPOS_RETVAL_Banka_Sonuc_Kod'],
                        ])->save();
                        
                        $order->getPayment()->getMethodInstance()->capture($order->getPayment(), $order->getGrandTotal());
                        $redirectUrl = $this->frontendUrlBuilder->setScope($order->getStoreId())->getUrl('checkout/onepage/success');
                        $messageMessenger->addSuccess($paymentResponse['TURKPOS_RETVAL_Sonuc_Str']);
                        return $this->resultRedirectFactory->create()->setUrl($redirectUrl);
                    } else {
                        $order->getPayment()->addData([
                            'tranRes' => $paymentResponse['TURKPOS_RETVAL_Sonuc_Str'],
                            'tranDate' => $paymentResponse['TURKPOS_RETVAL_Islem_Tarih'],
                            'tranResponseCode' => $paymentResponse['TURKPOS_RETVAL_Banka_Sonuc_Kod'],
                        ])->save();
                        
                        $order->getPayment()->setAdditionalInformation('tranRes', $order->getPayment()->getData('tranRes'));
                        $order->getPayment()->setAdditionalInformation('tranDate', $order->getPayment()->getData('tranDate'));
                        $order->getPayment()->setAdditionalInformation('tranResponseCode', $order->getPayment()->getData('tranResponseCode'));
                        $this->paymentResource->save($order->getPayment());
                        
                        $redirectUrl = $this->frontendUrlBuilder->setScope($order->getStoreId())->getUrl('checkout/onepage/failure');
                        $messageMessenger->addError($paymentResponse['TURKPOS_RETVAL_Sonuc_Str']);
                        return $this->resultRedirectFactory->create()->setUrl($redirectUrl);
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        if ($checkoutFailPage = $this->config->getCheckoutFailurePage()) {
            $redirectUrl = $this->pageHelper->getPageUrl($checkoutFailPage);
            return $this->resultRedirectFactory->create()->setUrl($redirectUrl);
        }

        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
    }
}

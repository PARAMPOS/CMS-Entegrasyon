<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Param\CcppCreditCard\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;


/**
 * Validate newly provided coupon code before using it while calculating totals.
 */
class Installment implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param CodeLimitManagerInterface $codeLimitManager
     * @param CartRepositoryInterface $cartRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritDoc
     */
    public function execute(EventObserver $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getData('quote');
        if($this->checkoutSession->getInstallmentFee()){
            $quote->setInstallmentFee($this->checkoutSession->getInstallmentFee());
        }
    }
}

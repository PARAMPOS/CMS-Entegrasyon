<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Param\CcppCreditCard\Model;

/**
 * PayPal payment information model
 *
 * Aware of all PayPal payment methods
 * Collects and provides access to PayPal-specific payment data
 * Provides business logic information about payment flow
 */
class Info
{
    /**
     * Map of payment information available to customer
     *
     * @var string[]
     */
    protected $_paymentPublicMap = [self::TRANS_REF, self::TRANS_RESPONSE_CODE, self::TRANS_DOC_ID, self::TRANS_AMOUNT, self::TRANS_DATE, self::TRANS_RESULT];

    /**
     * Rendered payment map cache
     *
     * @var array
     */
    protected $_paymentMapFull = [];

    /**
     * Cache for storing label translations
     *
     * @var array
     */
    protected $_labelCodesCache = [];

    /**
     * Param transaction id code key
     */
    const TRANS_REF = 'tranRef';

    const TRANS_RESPONSE_CODE = 'tranResponseCode';

    const TRANS_DOC_ID = 'docId';

    const TRANS_AMOUNT = 'tranAmount';

    const TRANS_DATE = 'tranDate';

    const TRANS_RESULT = 'tranRes';

    /**
     * Item labels key for label codes cache
     */
    const ITEM_LABELS = 'item labels';


    /**
     * Public payment info getter
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    public function getPublicPaymentInfo(\Magento\Payment\Model\InfoInterface $payment, $labelValuesOnly = false)
    {
        return $this->_getFullInfo($this->_paymentPublicMap, $payment, $labelValuesOnly);
    }

    /**
     * All available payment info getter
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    public function getPaymentInfo(\Magento\Payment\Model\InfoInterface $payment, $labelValuesOnly = false)
    {
        // collect Param-specific info
        $result = $this->_getFullInfo(array_values($this->_paymentMap), $payment, $labelValuesOnly);
        return $result;
    }

    /**
     * Render info item
     *
     * @param array $keys
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    protected function _getFullInfo(array $keys, \Magento\Payment\Model\InfoInterface $payment, $labelValuesOnly)
    {
        $result = [];
        foreach ($keys as $key) {
            if (!isset($this->_paymentMapFull[$key])) {
                $this->_paymentMapFull[$key] = [];
            }
            if (!isset($this->_paymentMapFull[$key]['label'])) {
                if (!$payment->hasAdditionalInformation($key)) {
                    $this->_paymentMapFull[$key]['label'] = false;
                    $this->_paymentMapFull[$key]['value'] = false;
                } else {
                    $value = $payment->getAdditionalInformation($key);
                    $this->_paymentMapFull[$key]['label'] = (string)$this->_getLabel($key);
                    $this->_paymentMapFull[$key]['value'] = $this->_getValue($value, $key);
                }
            }
            if (!empty($this->_paymentMapFull[$key]['value'])) {
                if ($labelValuesOnly) {
                    $value = $this->_paymentMapFull[$key]['value'];
                    $value = is_array($value) ? array_map('__', $value) : __($value);
                    $result[$this->_paymentMapFull[$key]['label']] = $value;
                } else {
                    $result[$key] = $this->_paymentMapFull[$key];
                }
            }
        }
        return $result;
    }

    /**
     * Render info item labels
     *
     * @param string $key
     * @return string
     * 
     */
    protected function _getLabel($key)
    {
        if (!isset($this->_labelCodesCache[self::ITEM_LABELS])) {
            $this->_labelCodesCache[self::ITEM_LABELS] = [
                self::TRANS_REF => __('Transaction Ref'),
                self::TRANS_RESULT => __('Transaction Result'),
                self::TRANS_DATE => __('Transaction Date'),
                self::TRANS_AMOUNT => __('Transaction Amount'),
                self::TRANS_DOC_ID => __('Doc ID'),
                self::TRANS_RESPONSE_CODE => __('Transaction Response Code'),
            ];
        }
        return $this->_labelCodesCache[self::ITEM_LABELS][$key] ?? '';
    }

    /**
     * Apply a filter upon value getting
     *
     * @param string $value
     * @param string $key
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getValue($value, $key)
    {
        $label = '';
        $outputValue = implode(', ', (array) $value);
        switch ($key) {
            default:
                return $outputValue;
        }
        return sprintf('#%s%s', $outputValue, $outputValue == $label ? '' : ': ' . $label);
    }

    /**
     * Attempt to convert AVS check result code into label
     *
     * @param string $value
     * @return string
     */
    protected function _getAvsLabel($value)
    {
        if (!isset($this->_labelCodesCache[self::PAYPAL_AVS_CODE])) {
            $this->_labelCodesCache[self::PAYPAL_AVS_CODE] = [
                'A' => __('Matched Address only (no ZIP)'), // Visa, MasterCard, Discover and American Express
                'B' => __('Matched Address only (no ZIP) International'), // international "A"
                'N' => __('No Details matched'),
                'C' => __('No Details matched. International'), // international "N"
                'X' => __('Exact Match.'),
                'D' => __('Exact Match. Address and Postal Code. International'), // international "X"
                'F' => __('Exact Match. Address and Postal Code. UK-specific'), // UK-specific "X"
                'E' => __('N/A. Not allowed for MOTO (Internet/Phone) transactions'),
                'G' => __('N/A. Global Unavailable'),
                'I' => __('N/A. International Unavailable'),
                'Z' => __('Matched five-digit ZIP only (no Address)'),
                'P' => __('Matched Postal Code only (no Address)'), // international "Z"
                'R' => __('N/A. Retry'),
                'S' => __('N/A. Service not Supported'),
                'U' => __('N/A. Unavailable'),
                'W' => __('Matched whole nine-digit ZIP (no Address)'),
                'Y' => __('Yes. Matched Address and five-digit ZIP'),
                '0' => __('All the address information matched'), // Maestro and Solo
                '1' => __('None of the address information matched'),
                '2' => __('Part of the address information matched'),
                '3' => __('N/A. The merchant did not provide AVS information'),
                '4' => __('N/A. Address not checked, or acquirer had no response. Service not available'),
            ];
        }
        return $this->_labelCodesCache[self::PAYPAL_AVS_CODE][$value] ?? $value;
    }

}

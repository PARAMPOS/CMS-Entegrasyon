<?php

namespace Param\CcppCreditCard\Model\Config\Source;

/**
 * Class CurrencyNo
 *
 * @package Param\CcppCreditCard\Model\Config\Source
 */
class CurrencyNo implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var int
     */
    const TURKISH_LIRA = 949;

    /**
     * @var int
     */
    const THAI_BAHT = 764;

    /**
     * @var int
     */
    const US_DOLLAR = 840;

    /**
     * @var int
     */
    const SINGAPORE_DOLLAR = 702;

    /**
     * @var int
     */
    const JAPAN_YEN = 392;

    /**
     * @var int
     */
    consT POUND_STERLING = 826;

    /**
     * @var int
     */
    consT MALAYSIAN_RINGGIT = 458;

    /**
     * @var int
     */
    consT INDONESIA_RUPIAH = 360;

    /**
     * @var int
     */
    consT EURO = 978;

    /**
     * @var int
     */
    consT MYANMAR = 104;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $key => $label) {
            $options[] = ['value' => $key, 'label' => $label];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            0 => __('Please select currency'),
            self::TURKISH_LIRA => __('Türk Lirası'),
            self::THAI_BAHT => __('Thai Baht'),
            self::SINGAPORE_DOLLAR => __('Singapore Dollar'),
            self::JAPAN_YEN => __('Japan Yen'),
            self::POUND_STERLING => __('Pound Sterling'),
            self::MALAYSIAN_RINGGIT => __('Malaysian Ringgit'),
            self::INDONESIA_RUPIAH => __('Indonesia Rupiah'),
            self::EURO => __('Euro'),
            self::MYANMAR => __('Myanmar')
        ];
    }
}

<?php

namespace Param\CcppCreditCard\Model;

/**
 * Class CcppConfigProvider
 *
 * Fix bug HTTP/2 in core system
 *
 * @package Param\CcppCreditCard\Model
 */
class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    /**
     * Parse headers - CURL callback function
     *
     * @param resource $ch curl handle, not needed
     * @param string $data
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function parseHeaders($ch, $data)
    {
        if ($this->_headerCount == 0) {
            $line = explode(" ", trim($data), 3);
            if (count($line) < 2) {
                return $this->doError("Invalid response line returned from server: " . $data);
            }
            $this->_responseStatus = intval($line[1]);
        } else {
            //var_dump($data);
            $name = $value = '';
            $out = explode(": ", trim($data), 2);
            if (count($out) == 2) {
                $name = $out[0];
                $value = $out[1];
            }

            if (strlen($name)) {
                if ("Set-Cookie" == $name) {
                    if (!isset($this->_responseHeaders[$name])) {
                        $this->_responseHeaders[$name] = [];
                    }
                    $this->_responseHeaders[$name][] = $value;
                } else {
                    $this->_responseHeaders[$name] = $value;
                }
            }
        }
        $this->_headerCount++;

        return strlen($data);
    }
}

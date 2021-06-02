<?php
/**
 * Created by Payfull.
 * Date: 10/15/2018
 */

namespace param\paramBasics;

class G
{
    public $CLIENT_CODE;//Terminal ID, It will be forwarded by param.
    public $CLIENT_USERNAME;//User Name, It will be forwarded by param.
    public $CLIENT_PASSWORD;//Password, It will be forwarded by param.

    /**
     * G constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     */
    public function __construct($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD)
    {
        $this->CLIENT_CODE = $CLIENT_CODE;
        $this->CLIENT_USERNAME = $CLIENT_USERNAME;
        $this->CLIENT_PASSWORD = $CLIENT_PASSWORD;
    }
}
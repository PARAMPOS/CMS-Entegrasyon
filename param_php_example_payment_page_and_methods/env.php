<?php
$developmentMode = true;

switch ($developmentMode) {
    case true :
        $env['URL'] = 'https://test-dmz.param.com.tr:4443/turkpos.ws/service_turkpos_test.asmx?WSDL';
        $env['CLIENT_USERNAME'] = 'test';
        $env['CLIENT_CODE'] = 10738;
        $env['CLIENT_PASSWORD'] = 'test';
        $env['GUID'] = '0c13d406-873b-403b-9c09-a5766840d98c';
        break;
    default:
        $env['GUID'] = 'DDDB71CC-3DB4-40E4-BD7D-XXXXXXXXXX';
        $env['CLIENT_USERNAME'] = 'TPXXXXXXXX';
        $env['CLIENT_PASSWORD'] = '2DTPXXXXXXXXXXXXXXXX';
        $env['CLIENT_CODE'] = 14162;
        $env['URL'] = 'https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx?WSDL';
}
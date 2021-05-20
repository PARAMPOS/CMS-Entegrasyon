<?php
include(dirname(__FILE__).'/../../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../../modules/sanalpospro/sanalpospro.php');

if(ParamTools::getValue('key')){
	$api = New SanalPosApiClient(0);
	$vars = $api->getRegisterVariables();
	if(ParamTools::getValue('key') != $vars['key'])
		die('Invalid access');
	die(ParamTools::getValue('key').ParamConfig::get('POSPRO_API_PRIVATE'));
}

if(ParamTools::getValue('hash') && ParamTools::getValue('rand')){
	$function = ParamTools::getValue('f');
	$hash = ParamConfig::get('POSPRO_API_PRIVATE').ParamTools::getValue('rand');
	$api =  New SanalPosApiClient(0);
	if($function && method_exists($api, $function) && ParamTools::getValue('hash') == md5(ParamConfig::get('POSPRO_API_PRIVATE').ParamTools::getValue('rand')))
		die(json_encode($api->$function()));
	die('invalid request.');
}
die('invalid request');
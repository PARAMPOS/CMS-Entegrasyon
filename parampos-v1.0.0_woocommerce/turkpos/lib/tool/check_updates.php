<?php
include(dirname(__FILE__).'/../../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../../modules/sanalpospro/sanalpospro.php');

if(EticTools::getValue('key')){
	$api = New SanalPosApiClient(0);
	$vars = $api->getRegisterVariables();
	if(EticTools::getValue('key') != $vars['key'])
		die('Invalid access');
	die(EticTools::getValue('key').EticConfig::get('POSPRO_API_PRIVATE'));
}

if(EticTools::getValue('hash') && EticTools::getValue('rand')){
	$function = EticTools::getValue('f');
	$hash = EticConfig::get('POSPRO_API_PRIVATE').EticTools::getValue('rand');
	$api =  New SanalPosApiClient(0);
	if($function && method_exists($api, $function) && EticTools::getValue('hash') == md5(EticConfig::get('POSPRO_API_PRIVATE').EticTools::getValue('rand')))
		die(json_encode($api->$function()));
	die('invalid request.');
}
die('invalid request');
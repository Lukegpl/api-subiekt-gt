<?php
use APISubiektGT\Config;
use APISubiektGT\Helper;
use APISubiektGT\Logger;
use APISubiektGT\SubiektGT;

require_once(dirname(__FILE__).'/../init.php');
$json_response = array();


header("Content-Type: application/json;charset=utf-8");

$header = Helper::getallheaders();		
if(isset($header['Accept']) && false !== strpos($header['Accept'],'application/json') || true){

	
	include('json_test.php');
	try{
		//Config load
		$cfg = new Config(CONFIG_INI_FILE);
		$cfg->load();

		//Create instance of Subiekt process and connect to it
		$subiektGt = SubiektGT::getInstance($cfg);
		
		//Check is set api_key
		if(!isset($json_request['api_key'])){
			throw new Exception('Nie podano klucza API=>api_key',1);
		}

		//Connect or create SubiektGt Windows process
		$subiektGtCom = $subiektGt->connect();		

		//Run API request.
		$obj = new APISubiektGT\SubiektGT\Order($subiektGtCom,$json_request['data']);
		$result = $obj->add();


		$json_response['status'] = 'success';	
		if(is_array($result)){
			$json_response['data']	 = $result;
		}
		Logger::getInstance()->log('api','Request OK',__FILE__,'',__LINE__);
	}catch(Exception $e){
		$json_response['status'] = 'fail';
		$json_response['message'] = $e->getMessage();
		Logger::getInstance()->log('api_error',$e->getMessage(),$e->getFile(),$e->getLine());		
	}

}else{
	$json_response['status'] = 'fail';
	$json_response['message'] = 'Header application/json missing!';
	Logger::getInstance()->log('api_error',$json_response['message'],__FILE__,__LINE__);
}
echo json_encode($json_response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
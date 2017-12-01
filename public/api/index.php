<?php
use APISubiektGT\Config;
use APISubiektGT\Helper;
use APISubiektGT\Logger;
use APISubiektGT\SubiektGT;

require_once(dirname(__FILE__).'/../init.php');
$json_response = array();


header("Content-Type: application/json;charset=utf-8");

$header = Helper::getallheaders();
if(isset($header['Content-Type']) && ('application/json'==$header['Content-Type'] || 'application/json;charset=utf-8'==$header['Content-Type']) || true){
		
	include('json_test.php');
	try{

		$run = explode('/',$_GET['c']);
		if(count($run)!=2){
			throw new Exception("Nie prawidłowe wywołanie API");
		}
		
		$class = "APISubiektGT\\SubiektGT\\{$run[0]}";	
		$method = $run[1];
		if(!class_exists($class)){
			throw new Exception("Nie prawidłowe wywołanie API nie istnieje obiekt: {$run[0]}");	
		}

		if(!method_exists($class ,$method)){
			throw new Exception("Nie prawidłowe wywołanie API. Brak metody: {$method}");		
		}
		//$jsonStr = @file_get_contents("php://input");
		//if($jsonStr!=NULL){
			//$json_request= json_decode($json,true);
		//}
		//Config load
		$cfg = new Config(CONFIG_INI_FILE);
		$cfg->load();

		//Create instance of Subiekt process and connect to it
		$subiektGt = SubiektGT::getInstance($cfg);
		
		//Check is set api_key
		if(!isset($json_request['api_key'])){
			throw new Exception('Nie podano klucza API=>api_key');
		}

		//Connect or create SubiektGt Windows process
		$subiektGtCom = $subiektGt->connect();		

		//Run API request.
		
		$result = false;

		$obj = new $class($subiektGtCom,$json_request['data']);
		$reflection = new ReflectionMethod($obj , $method);
		if(!$reflection->isPublic()){
			throw new Exception("Wywołanie metody: {$method} jest zabronione!");
		}

		$result = $obj->$method();
		$json_response['status'] = 'success';	

		if(is_array($result)){
			$json_response['data']	 = $result;
		}
		Logger::getInstance()->log('api','Request OK',__FILE__,'',__LINE__);
	}catch(Exception $e){
		$json_response['status'] = 'fail';
		$json_response['message'] = $e->getMessage();			
		Logger::getInstance()->log('api_error',Helper::toUtf8($e->getMessage()),$e->getFile(),$e->getLine());		
	}

}else{
	$json_response['status'] = 'fail';
	$json_response['message'] = 'Header Content-Type:application/json missing!';
	Logger::getInstance()->log('api_error',$json_response['message'],__FILE__,__LINE__);
}
$json_string = json_encode($json_response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if(JSON_ERROR_UTF8 == json_last_error()){
	$json_string = json_encode(Helper::toUtf8($json_response),JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
echo $json_string;
?>
<?php

use APISubiektGT\Config;
use APISubiektGT\Helper;
use APISubiektGT\Logger;
use APISubiektGT\SubiektGT;

require_once(dirname(__FILE__) . '/../init.php');
$json_response = array();
$obj = false;
Logger::getInstance()->log('api', 'Request start: ' . $_SERVER['REMOTE_ADDR'], '', __LINE__);

header("Content-Type: application/json;charset=utf-8");

$header = Helper::getallheaders();
try {
	if (
		false && (!isset($header['Content-Type']) ||
			!('application/json' == $header['Content-Type'] || 'application/json;charset=utf-8' == $header['Content-Type']))
	) {
		throw new Exception("Header Content-Type:application/json missing!");
	}


	//Get Json stream from "input".
	$jsonStr = @file_get_contents("php://input");
	$jsonStr = trim($jsonStr);
	if ($jsonStr != NULL) {
		$json_request = json_decode($jsonStr, true);
		if (json_last_error() > 0) {
			throw new Exception("JSON read: " . json_last_error_msg());
		}
	} else {
		throw new Exception("Brak danych w żądaniu!");
	}


	//include('json_test.php');

	$run = explode('/', $_GET['c']);
	if (count($run) != 2) {
		throw new Exception("Nie prawidłowe wywołanie API");
	}

	$class = "APISubiektGT\\SubiektGT\\{$run[0]}";
	$method = $run[1];
	if (!class_exists($class)) {
		throw new Exception("Nieprawidłowe wywołanie API nie istnieje obiekt: {$run[0]}");
	}

	if (!method_exists($class, $method)) {
		throw new Exception("Nieprawidłowe wywołanie API. Brak metody: {$method}");
	}


	//Check is set api_key
	if (!isset($json_request['api_key'])) {
		throw new Exception('Nie podano klucza API=>api_key');
	}
	//Config load
	$cfg = new Config(CONFIG_INI_FILE);
	$cfg->load();


	if (!$cfg->verifyAPIKey($json_request['api_key'])) {
		throw new Exception("Nieprawidłowy klucz API - api_key!");
	}

	//Create instance of Subiekt process and connect to it
	$subiektGt = SubiektGT::getInstance($cfg);

	//Connect or create SubiektGt Windows process
	$subiektGtCom = $subiektGt->connect();

	//Processing API request.		
	$result = false;

	//Create API class object 
	$obj = new $class($subiektGtCom, $json_request['data']);
	$obj->setCfg($cfg);
	$reflection = new ReflectionMethod($obj, $method);
	if (!$reflection->isPublic()) {
		throw new Exception("Wywołanie metody: {$method} jest zabronione!");
	}

	//Run API request
	$result = $obj->$method();

	$json_response['state'] = 'success';
	$json_response['data']	 = $result;
	
	//Zakomentowane aby nie zamykac bieżącego (uruchomionego) uchwytu do obiektu COM.
	//$subiektGtCom->Zakoncz();
	
	Logger::getInstance()->log('api', 'Request finish: ' . $_SERVER['REMOTE_ADDR'], $class . '->' . $method, __LINE__);
} catch (Exception $e) {
	$json_response['state'] = 'fail';
	$json_response['message'] = strip_tags($e->getMessage());
	$json_response['obj_dump'] = print_r($obj, true);
	if (isset($json_request['data'])) {
		$json_response['data'] = $json_request['data'];
	}
	Logger::getInstance()->log('api_error', Helper::toWin($e->getMessage()), $e->getFile(), $e->getLine());
}

$json_string = json_encode($json_response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (JSON_ERROR_UTF8 == json_last_error()) {
	$json_string = json_encode(Helper::toUtf8($json_response), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
echo $json_string;

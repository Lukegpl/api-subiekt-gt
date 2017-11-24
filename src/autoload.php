<?php
namespace APISubiektGT;


function app_autoloader($lib_name) {	
	$lib_path = dirname(__FILE__).'/'.str_replace("\\", "/", strtolower($lib_name));	
	if(class_exists($lib_name)){		
		return;
	}		
	if(file_exists($lib_path.'.php')){
		require_once($lib_path.'.php');
		return;							
	}	
	$namespace = explode("/",$lib_path);	
	if(file_exists($lib_path.'/'.end($namespace).'.php')){		
		require_once($lib_path.'/'.end($namespace).'.php');				
		return;							
	}		
}
spl_autoload_register(__NAMESPACE__.'\app_autoloader');
?>
<?php
namespace APISubiektGT;


class Helper {
	
  public static function getallheaders(){ 
       $headers = array(); 
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));               
               $headers[$key] = $value; 
           } 
       } 
       return $headers; 
    } 

}
?>
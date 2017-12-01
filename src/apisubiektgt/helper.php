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

  static public function toUtf8($value){
    if(is_object($value)){
      return $value;
    }
    if(!is_array($value)){
      return mb_convert_encoding($value,'UTF-8','ISO-8859-2');
    }

    foreach($value as $key => $v){
      $value[$key] = self::toUtf8($v);
    }
    return $value;
  }

}
?>
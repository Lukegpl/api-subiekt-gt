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
    if(is_string($value)){
      return mb_convert_encoding($value,'UTF-8','ISO-8859-2');
    }
    if(is_array($value)){
      foreach($value as $key => $v){
        $value[$key] = self::toUtf8($v);
      }
    }
    return $value;
  }

  static public function getValue($key, $defaultValue = false)
  {
    if (!isset($key) OR empty($key) OR !is_string($key))
      return false;
    $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

    if (is_string($ret) === true)
      $ret = urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret)));
    if(is_array($ret)){
      return $ret;
    }
    return trim(!is_string($ret)? $ret : ($ret));
  }
  

  static public function getIsset($key)
  {
    if (!isset($key) OR empty($key) OR !is_string($key))
      return false;
    return isset($_POST[$key]) ? true : (isset($_GET[$key]) ? true : false);
  }


}
?>
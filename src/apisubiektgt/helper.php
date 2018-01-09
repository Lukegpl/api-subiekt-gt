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
    $iso_to_win = array(185=>177,156=>182,159=>188,165=>161,140=>166,143=>172);
    if(is_string($value)){
      for($i=0;$i<strlen($value);$i++){
        $chr_no = ord($value[$i]);
        if(isset($iso_to_win[$chr_no])){
          $value[$i] = chr($iso_to_win[$chr_no]);
        }
      }
      return mb_convert_encoding($value,'UTF-8','ISO-8859-2');
    }
    if(is_array($value)){
      foreach($value as $key => $v){
        $value[$key] = self::toUtf8($v);
      }
    }
    return $value;
  }

  static public function toWin($value){
    $iso_to_win = array(177=>185,182=>156,188=>159,161=>165,166=>140,172=>143);

    if(is_string($value)){


      $value = mb_convert_encoding(trim($value),'ISO-8859-2','UTF-8');
      //$keys = array_keys($iso_to_win);
      for($i=0;$i<strlen($value);$i++){
        $chr_no = ord($value[$i]);
        if(isset($iso_to_win[$chr_no])){
          $value[$i] = chr($iso_to_win[$chr_no]);
        }
      }
      return $value;
    }
    if(is_array($value)){
      foreach($value as $key => $v){
        $value[$key] = self::toWin($v);
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
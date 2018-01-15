<?php
namespace APISubiektGT;

class Logger {
	static protected $_instance = false;
	protected $log_path;

	public function __construct($log_path){
		$this->log_path = $log_path;
	}

	static public function getInstance($log_path = ''){
		if(!self::$_instance){
			self::$_instance = new Logger($log_path);
		}
		return self::$_instance;
	}

	public function log($type,$message,$source,$line){
		$line = date("Y-m-d H:i:s"). " Line: {$line}, {$source}: $message\n";
		$date = date('y_m_d');	
		file_put_contents("{$this->log_path}{$type}_{$date}.log",$line,FILE_APPEND);
	}
}
?>
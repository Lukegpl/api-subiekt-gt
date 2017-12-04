<?php
namespace APISubiektGT;
use Exception;

class Config{
	
	protected $server;
	protected $dbuser;
	protected $dbpassword;
	protected $database;	
	protected $_ini_file;
	protected $api_key;
	protected $new_product_prefix = '';


	/**
	*	Construct
	*/
	public function __construct($ini_file){
		$this->_ini_file = $ini_file;
	}

	/**
	*	Try load current configuration
	*/
	public function load(){
		try{
			$ini_data = @parse_ini_file($this->_ini_file);
			if(!$ini_data){
				throw new Exception("Nie można załadować konfiguracji z pliku:{$this->_ini_file}", 1);			
			}
			foreach($ini_data as $key=>$value){
				$this->{$key} = str_replace(';', '', $value);
			}
		}catch(Exception $e){
			Logger::getInstance()->log('api_error',$e->getMessage(),__CLASS__.'->'.__FUNCTION__,__LINE__);
			return 0;
		}		
		return 1;
	}


	public function getAPIKey(){
		return $this->api_key;
	}


	public function verifyAPIKey($api_key){
		if($this->api_key == trim($api_key)){
			return true;
		}
		return false;
	}

	/**
	*	Get server variable
	*/
	public function getServer(){
		return $this->server;
	}

	/**
	*	Get database variable
	*/
	public function getDatabase(){
		return $this->database;
	}

	/**
	*	Get db user name
	*/
	public function getDbUser(){
		return $this->dbuser;
	}

	/**
	*	Get db user password
	*/
	public function getDbUserPass(){
		return $this->dbpassword;
	}


	/**
	*	Get prefic for new product name
	*/
	public function getNewProductPrefix(){
		return $this->new_product_prefix;
	}


	public function save(){

	}
}
?>
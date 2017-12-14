<?php
namespace APISubiektGT\SubiektGT;

use APISubiektGT\Logger;
use APISubiektGT\SubiektGT;
use APISubiektGT\Helper;

abstract class SubiektObj{

	protected $subiektGt = false;
	protected $is_exists = false;
	protected $gt_id = false;
	protected $exclude_attibs = array('subiektGt',
							'exclude_attibs',
							);	

	public function __construct($subiektGt, $objDetail = array()){
		if(is_array($objDetail)){
			foreach($objDetail as $key=>$value){
				if(!is_array($value) && is_string($value)){
					$this->{$key} = Helper::toWin($value);
				}else{
					$this->{$key} = $value;	
				}
			}
		}
		$this->subiektGt = $subiektGt;		
	}
	

	protected function excludeAttr($name){
		if(is_array($name)){
			$this->exclude_attibs = array_merge($this->exclude_attibs,$name);
		}else{
			$this->exclude_attibs = array_merge($this->exclude_attibs,array($name));
		}
	}

	abstract protected function setGtObject();
	abstract protected function getGtObject();	
	abstract public function add();
	abstract public function update();
	abstract public function getGt();

	public function isExists(){
		return $this->is_exists;
	}


	public function get(){
		$ret_data = array();
		foreach ($this as $key => $value) {
			if(in_array($key,$this->exclude_attibs)){
				continue;
			}
			$ret_data[$key] = self::toUtf8($value);
		}	
		Logger::getInstance()->log('api','Pobrano dane obiektu id: '.$this->gt_id ,__CLASS__.'->'.__FUNCTION__,__LINE__);
		return $ret_data;
	}

	static public function toUtf8($value){
		return Helper::toUtf8($value);
	}
	
}
?>
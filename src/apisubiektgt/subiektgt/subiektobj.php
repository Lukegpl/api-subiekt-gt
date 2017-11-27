<?php
namespace APISubiektGT\SubiektGT;

abstract class SubiektObj{

	protected $subiektGt = false;
	protected $is_exists = false;
	protected $exclude_attibs = array('subiektGt','exclude_attibs','is_exists');

	public function __construct($subiektGt, $objDetail){
		foreach($objDetail as $key=>$value){
			if(!is_array($value) && is_string($value)){
				$this->{$key} = mb_convert_encoding($value,'ISO-8859-2');
			}
		}
		$this->subiektGt = $subiektGt;
	}

	protected function excludeAttr($name){
		$this->exclude_attibs = array_merge($this->exclude_attibs,array($name));
	}

	abstract protected function setGtObject();
	abstract protected function getGtObject();	
	abstract public function add();
	abstract public function update();

	public function isExists(){
		return $this->is_exists;
	}


	public function get(){
		$ret_data = array();
		foreach ($this as $key => $value) {
			if(in_array($key,$this->exclude_attibs)){
				continue;
			}
			$ret_data[$key] = mb_convert_encoding($value,'UTF-8','ISO-8859-2');
		}
		return $ret_data;
	}
	
}
?>
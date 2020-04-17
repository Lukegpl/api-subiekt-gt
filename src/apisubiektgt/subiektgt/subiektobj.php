<?php
namespace APISubiektGT\SubiektGT;

use APISubiektGT\Logger;
use APISubiektGT\SubiektGT;
use APISubiektGT\Helper;
use APISubiektGT\MSSql;

abstract class SubiektObj{

	protected $subiektGt = false;
	protected $is_exists = false;
	protected $gt_id = false;
	protected $cfg = false;
	protected $id_user = 1;
	protected $objDetail = false;
	protected $exclude_attibs = array('subiektGt',
							'exclude_attibs','cfg','doc_types'
							);
		
	protected $doc_types = array(1=>'FZ',
						 2=>'FS',
						 5=>'KFZ',
						 6=>'KFS',
						 9=>'MM',
						10=> 'PZ',
						11=>'WZ',
						12=>'PW',
						13=>'RW',
						14=>'ZW',
						15=>'ZD',
						16=>'ZK',
						21=>'PA',
						29=>'IW',
						35=>'ZPZ',
						36=>'ZWZ',
						62=>'FM',
						);	

	public function __construct($subiektGt, $objDetail = array()){
		$this->readData($objDetail);
		$this->subiektGt = $subiektGt;		
	}
	
	protected function readData($objDetail){

		if(is_array($objDetail)){
			foreach($objDetail as $key=>$value){
				if(!is_array($value) && is_string($value)){
					$this->{$key} = Helper::toWin($value);				
				}else{
					$this->{$key} = $value;	
				}
			}
			$this->objDetail = $objDetail;
		}
	} 

	protected function excludeAttr($name){
		if(is_array($name)){
			$this->exclude_attibs = array_merge($this->exclude_attibs,$name);
		}else{
			$this->exclude_attibs = array_merge($this->exclude_attibs,array($name));
		}
	}

	public function setCfg($cfg){
		$this->cfg = $cfg;
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


	protected function flag($id_gr_flag, $flag_name,$comment=''){
		return $this->subiektGt->UstawFlageWlasna($id_gr_flag,$this->gt_id,$flag_name,$comment);
	}
	
}
?>
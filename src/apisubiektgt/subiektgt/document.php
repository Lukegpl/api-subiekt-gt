<?php
namespace APISubiektGT\SubiektGT;
use COM;
use Exception;
use APISubiektGT\Logger;
use APISubiektGT\MSSql;
use APISubiektGT\SubiektGT\SubiektObj;
use APISubiektGT\SubiektGT\Product;
use APISubiektGT\SubiektGT\Customer;

class Document extends SubiektObj {
	protected $documentGt;
	protected $products = false;	
	protected $fiscal_state = false;
	protected $accounting_state = false;
	protected $reference;
	protected $comments;
	protected $customer = array();	
	protected $doc_ref = '';
	protected $doc_type = 0;
	protected $amount = 0;
	protected $state = -1;
	protected $date_of_delivery = '';		
	protected $documentDetail= array();
	protected $order_processing = 0;
	protected $id_flag = 0;
	protected $flag_txt = '';
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

	public function __construct($subiektGt,$documentDetail = array()){
		parent::__construct($subiektGt, $documentDetail);
		$this->excludeAttr(array('documentGt','documentDetail','doc_types'));		

		if($this->doc_ref!='' && $subiektGt->SuDokumentyManager->Istnieje($this->doc_ref)){
			$this->documentGt = $subiektGt->SuDokumentyManager->Wczytaj($this->doc_ref);			
			$this->getGtObject();
			$this->is_exists = true;			
		}		
		$this->documentDetail = $documentDetail;
	}



	protected function setGtObject(){
		return false;
	}

	public function getPdf(){
		$temp_dir = sys_get_temp_dir();
		if($this->is_exists){
			$file_name = $temp_dir.'/'.$this->gt_id.'.pdf';
			$this->documentGt->DrukujDoPliku($file_name,0);
			$pdf_file = file_get_contents($file_name);
			Logger::getInstance()->log('api','Wygenerowano pdf dokumentu: '.$this->doc_ref ,__CLASS__.'->'.__FUNCTION__,__LINE__);
			return array('encoding'=>'base64',
					 'doc_ref'=>$this->doc_ref,
					 'is_exists' => $this->is_exists,
					 'file_name' => mb_ereg_replace("[ /]","_",$this->doc_ref.'.pdf'),
					 'state' => $this->state,
					 'accounting_state' => $this->accounting_state,
					 'fiscal_state' => $this->fiscal_state,
					 'doc_type' => $this->doc_type,
					 'pdf_file'=>base64_encode($pdf_file));
		}
		return false;
	}


	public function getState(){
		return array('doc_ref'=>$this->doc_ref,
				 'is_exists' => $this->is_exists,
				 'doc_type' => $this->doc_type,
				 'state' => $this->state,
				 'accounting_state' => $this->accounting_state,
				 'fiscal_state' => $this->fiscal_state,
				 'order_processing' => $this->order_processing,	
				 'id_flag'	 	=> $this->id_flag,
				 'flag_txt'		=> $this->flag_txt		 
				);
	}

	protected function getGtObject(){	
		if(!$this->documentGt){
			return false;
		}	
		$this->gt_id = $this->documentGt->Identyfikator;
		$this->fiscal_state = $this->documentGt->StatusFiskalny;
		$this->accounting_state = $this->documentGt->StatusKsiegowy;
		$this->doc_type = $this->doc_types[$this->documentGt->Typ];
		
		$o = $this->getDocumentById($this->gt_id);
		
		$this->reference =  $o['dok_NrPelnyOryg'];
		$this->comments = $o['dok_Uwagi'];
		$this->doc_ref = $o['dok_NrPelny'];			
		$this->state = $o['dok_Status'];				
		$this->amount = $o['dok_WartBrutto'];
		$this->date_of_delivery = $o['dok_TerminRealizacji'];
		$this->order_processing = $o['dok_PrzetworzonoZKwZD'];
		$this->id_flag = $o['flg_Id'];
		$this->flag_txt = $o['flg_Text'];
				
		if(!is_null($this->documentGt->KontrahentId)){
			$customer = Customer::getCustomerById($this->documentGt->KontrahentId);
			$this->customer = $customer;
		}
		
		$positions = array();
		for($i=1; $i<=$this->documentGt->Pozycje->Liczba(); $i++){
			$positions[$this->documentGt->Pozycje->Element($i)->Id]['name'] = $this->documentGt->Pozycje->Element($i)->TowarNazwa;
			$positions[$this->documentGt->Pozycje->Element($i)->Id]['code'] = $this->documentGt->Pozycje->Element($i)->TowarSymbol;
		}
		

		$products = $this->getPositionsByOrderId($this->gt_id);
		foreach($products as $p){			
			$p_a = array('name'=> $positions[$p['ob_Id']]['name'],
					   'code'=> $positions[$p['ob_Id']]['code'],
					   'qty'=>$p['ob_Ilosc'],
					   'price'=>$p['ob_WartBrutto']);
			$this->products[] = $p_a;
		}

	}

	protected function getDocumentById($id){
		$sql = "SELECT * FROM dok__Dokument as d
					LEFT JOIN fl_Wartosc as fw ON (fw.flw_IdObiektu = d.dok_Id)
					LEFT JOIN fl__Flagi as f ON (f.flg_Id = fw.flw_IdFlagi)
				WHERE dok_Id = {$id}";				
		$data = MSSql::getInstance()->query($sql);
		return $data[0];
	}

	protected function getPositionsByOrderId($id){
		$sql = "SELECT * FROM dok_Pozycja
			   WHERE ob_DokHanId = {$id}";			
		$data = MSSql::getInstance()->query($sql);
		return $data;
	}

	public function delete(){
		if(!$this->documentGt){
			return false;
		}

		$this->documentGt->Usun(false);	
		return array('doc_ref'=>$this->doc_ref);
	}

	public function add(){	
		return true;
	}

	public function update(){
		return true;
	}

	public function getGt(){
		return $this->documentGt;	
	}
}
?>
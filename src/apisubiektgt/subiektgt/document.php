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
	protected $reference;
	protected $comments;
	protected $customer = array();	
	protected $doc_ref = '';
	protected $amount = 0;
	protected $state = -1;
	protected $date_of_delivery = '';
	protected $documentDetail= array();

	public function __construct($subiektGt,$documentDetail = array()){
		parent::__construct($subiektGt, $documentDetail);
		$this->excludeAttr(array('documentGt','documentDetail'));		
		$symbol = '';
		if(isset($documentDetail['doc_ref'])){
			$symbol = trim($documentDetail['doc_ref']);
		}
		if($symbol!='' && $subiektGt->SuDokumentyManager->Istnieje($symbol)){
			$this->documentGt = $subiektGt->SuDokumentyManager->Wczytaj($symbol);			
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
			return array('encoding'=>'base64','doc_ref'=>$this->doc_ref,'pdf_file'=>base64_encode($pdf_file));
		}
		return false;
	}

	protected function getGtObject(){	
		if(!$this->documentGt){
			return false;
		}	
		$this->gt_id = $this->documentGt->Identyfikator;
		$o = $this->getDocumentById($this->gt_id);
		
		$this->reference =  $o['dok_NrPelnyOryg'];
		$this->comments = $o['dok_Uwagi'];
		$this->doc_ref = $o['dok_NrPelny'];		
		$this->state = $o['dok_Status'];				
		$this->amount = $o['dok_WartBrutto'];
		$this->date_of_delivery = $o['dok_TerminRealizacji'];
				
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
		$sql = "SELECT * FROM dok__Dokument WHERE dok_Id = {$id}";				
		$data = MSSql::getInstance()->query($sql);
		return $data[0];
	}

	protected function getPositionsByOrderId($id){
		$sql = "SELECT * FROM dok_Pozycja
			   WHERE ob_DokHanId = {$id}";			
		$data = MSSql::getInstance()->query($sql);
		return $data;
	}


	public function add(){	
		return false;
	}

	public function update(){
		return true;
	}

	public function getGt(){
		return $this->documentGt;	
	}
}
?>
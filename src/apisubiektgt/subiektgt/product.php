<?php
namespace APISubiektGT\SubiektGT;
use COM;
use APISubiektGT\MSSql;
use APISubiektGT\Logger;
use APISubiektGT\SubiektGT\SubiektObj;
use APISubiektGT\SubiektGT;

class Product extends SubiektObj{

	protected $productGt = false;	
	protected $ean;
	protected $code;
	protected $price;
	protected $name;	
	protected $qty;	
	protected $id_store = 0;

	public function __construct($subiektGt,$productDetail = array()){		
		parent::__construct($subiektGt, $productDetail);
		$this->excludeAttr('productGt');
		
		$symbol = '';
		if(isset($productDetail['code'])){
			$symbol = trim($productDetail['code']);
		}
		if($symbol!='' &&  $subiektGt->Towary->Istnieje($symbol)){
			$this->productGt = $subiektGt->Towary->Wczytaj($symbol);			
			$this->is_exists = true;			
			$this->getGtObject();
		}		
	}

	protected function setGtObject(){				
		if(!$this->is_exists){
			$new_prefix = SubiektGT::getInstance()->getConfig()->getNewProductPrefix();
			if(strlen($new_prefix)>0){
				$this->productGt->Nazwa = "{$new_prefix} {$this->name}";
			}
		}else{
			$this->productGt->Nazwa = $this->name;
		}
		$this->productGt->Symbol = $this->code;
		$this->productGt->Aktywny = true;
		$this->CenaKartotekowa = floatval($this->price);
		$this->productGt->KodyKreskowe->Dodaj($this->ean);
		return true;
	}

	protected function getGtObject(){
		if(!$this->productGt){
			return false;
		}
		$this->gt_id = $this->productGt->Identyfikator;
		$this->name = $this->productGt->Nazwa;		
		$this->code = $this->productGt->Symbol;
		if($this->productGt->KodyKreskowe->Liczba>0){
			$this->ean = $this->productGt->KodyKreskowe->Element(1);
		}
		if($this->productGt->Ceny->Liczba>0){
			$prices = $this->productGt->Ceny->Element(1);
			$this->price = floatval($prices->Brutto);			
		}
		$qty = $this->getQty();
		$this->qty = intval($qty['Dostepne']);
		return true;
	}


	public function getListByStore(){
		$sql = "SELECT tw_Symbol as code ,Rezerwacja as resevation,Dostepne as available FROM vwTowar WHERE st_MagId = ".intval($this->id_store);
		$data = MSSql::getInstance()->query($sql);
		return $data[0];	
	}

	protected function getQty(){
		$sql = "SELECT TOP 1 Rezerwacja,Dostepne FROM vwTowar WHERE tw_Id = {$this->gt_id} AND st_MagId = ".intval($this->id_store);		
		$data = MSSql::getInstance()->query($sql);
		return $data[0];
	}

	public function add(){
		$this->productGt = $this->subiektGt->TowaryManager->DodajTowar();
		$this->setGtObject();		
		$this->productGt->Zapisz();
		Logger::getInstance()->log('api','Utworzono produkt: '.$this->productGt->Symbol,__CLASS__.'->'.__FUNCTION__,__LINE__);
		return array('gt_id'=>$this->productGt->Identyfikator);
	}

	public function update(){
		if(!$this->productGt){
			return false;
		}
		$this->setGtObject();
		$this->productGt->Zapisz();
		Logger::getInstance()->log('api','Zaktualizowano produkt: '.$this->productGt->Symbol,__CLASS__.'->'.__FUNCTION__,__LINE__);
		return true;
	}

	public function getGt(){
		return $this->productGt;
	}
}
?>
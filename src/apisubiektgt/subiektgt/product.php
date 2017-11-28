<?php
namespace APISubiektGT\SubiektGT;
use COM;
use APISubiektGT\MSSql;
use APISubiektGT\SubiektGT\SubiektObj;

class Product extends SubiektObj{

	protected $productGt = false;	
	protected $ean;
	protected $code;
	protected $price;
	protected $name;
	protected $price_before_discount;
	protected $qty;	
	protected $id_mag = 0;

	public function __construct($subiektGt,$productDetail = array()){		
		parent::__construct($subiektGt, $productDetail);
		$this->excludeAttr('productGt');
		
		if(isset($productDetail['code'])){
			$symbol = trim($productDetail['code']);
		}
		if($subiektGt->Towary->Istnieje($symbol)){
			$this->productGt = $subiektGt->Towary->Wczytaj($symbol);
			$this->getGtObject();
			$this->is_exists = true;			
		}		
	}

	protected function setGtObject(){

	}

	protected function getGtObject(){
		$this->gt_id = $this->productGt->Identyfikator;
		$this->name = $this->productGt->Nazwa;
		$this->code = $this->productGt->Symbol;
		if($this->productGt->KodyKreskowe->Liczba>0){
			$this->ean = $this->productGt->KodyKreskowe->Element(1);
		}
		if($this->productGt->Ceny->Liczba>0){
			$prices = $this->productGt->Ceny->Element(1);
			$this->price = floatval($prices->Brutto);
			$this->price_before_discount = $this->price;
		}
		$qty = $this->getQty();
		$this->qty = intval($qty['Dostepne']);
	}

	protected function getQty(){
		$sql = "SELECT TOP 1 Rezerwacja,Dostepne FROM vwTowar WHERE tw_Id = {$this->gt_id} AND st_MagId = {$this->id_mag}";		
		$data = MSSql::getInstance()->query($sql);
		return $data[0];
	}


	public function add(){
	}

	public function update(){

	}

	public function getGt(){
		return $this->productGt;
	}
}
?>
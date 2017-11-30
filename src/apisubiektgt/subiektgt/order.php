<?php
namespace APISubiektGT\SubiektGT;
use COM;
use Exception;
use APISubiektGT\Logger;
use APISubiektGT\MSSql;
use APISubiektGT\SubiektGT\SubiektObj;
use APISubiektGT\SubiektGT\Product;
use APISubiektGT\SubiektGT\Customer;

class Order extends SubiektObj {
	protected $orderGt;
	protected $products = false;	
	protected $reference;
	protected $comments;
	protected $customer = false;
	protected $reservation = true;
	protected $order_ref = '';
	protected $amount = 0;
	protected $state = -1;
	protected $pay_type = 'transfer';
	protected $create_product_if_not_exists = false;
	protected $orderDetail= array();

	public function __construct($subiektGt,$orderDetail = array()){
		parent::__construct($subiektGt, $orderDetail);
		$this->excludeAttr(array('orderGt','orderDetail','pay_type','create_product_if_not_exists'));

		$symbol = '';
		if(isset($orderDetail['order_ref'])){
			$symbol = trim($orderDetail['order_ref']);
		}
		if($symbol!='' && $subiektGt->SuDokumentyManager->Istnieje($symbol)){
			$this->orderGt = $subiektGt->SuDokumentyManager->Wczytaj($symbol);
			$this->getGtObject();
			$this->is_exists = true;			
		}		
		$this->orderDetail = $orderDetail;
	}

	protected function addPosition($product){
		$position = false;
		$p = new Product($this->subiektGt,$product);
		if(!$p->isExists()){
			return false;
		}

		$p_data = $p->get();

		$position = $this->orderGt->Pozycje->Dodaj($p_data['gt_id']);
		$position->IloscJm = $product['qty'];
		$position->WartoscBruttoPrzedRabatem = $product['price_before_discount']*$product['qty'];
		$position->WartoscBruttoPoRabacie  = $product['price']*$product['qty'];	
		return $position;
	}

	protected function setGtObject(){
		$this->orderGt->Tytul = $this->reference;
		$this->orderGt->Uwagi  = $this->comments;	
		$this->orderGt->Rezerwacja = $this->reservation;	
		switch($this->pay_type){
			case 'transfer' : $this->orderGt->PlatnoscPrzelewKwota = $this->amount; break;
			case 'credit' : $this->orderGt->PlatnoscKartaKwota = $this->amount; break;
			case 'money' : $this->orderGt->PlatnoscGotowkaKwota = $this->amount; break;
			case 'credit' : $this->orderGt->PlatnoscKredytKwota = $this->amount; break;
		}
		$this->orderGt->NumerOryginalny = $this->reference;


	}

	public function getPdf(){
		$temp_dir = sys_get_temp_dir();
		if($this->is_exists){
			$file_name = $temp_dir.'/'.$this->gt_id.'.pdf';
			$this->orderGt->DrukujDoPliku($file_name,0);
			$pdf_file = file_get_contents($file_name);
			return array('encoding'=>'base64','pdf_file'=>base64_encode($pdf_file));
		}
		return false;
	}

	protected function getGtObject(){		
		$this->gt_id = $this->orderGt->Identyfikator;
		$o = $this->getOrderById($this->gt_id);
		
		$this->reference =  $o['dok_NrPelnyOryg'];
		$this->comments = $o['dok_Uwagi'];
		$this->order_ref = $o['dok_NrPelny'];
		$this->reservation = $o['statusrez'];	
		$this->state = $o['dok_Status'];				
		$this->amount = $o['dok_WartBrutto'];
		
		$customer = Customer::getCustomerById($this->orderGt->KontrahentId);
		$this->customer = $customer;
		$products = $this->getPositionsByOrderId($this->gt_id);
		foreach($products as $p){
		
			$p_a = array('name'=>$p['tw_Nazwa'],
					   'code'=>$p['tw_Symbol'],
					   'qty'=>$p['ob_IloscMag'],
					   'price'=>$p['ob_WartBrutto']);
			$this->products[] = $p_a;
		}

	}

	protected function getOrderById($id){
		$sql = "SELECT * FROM vwDok4ZamGrid WHERE dok_Id = {$id}";		
		$data = MSSql::getInstance()->query($sql);
		return $data[0];
	}

	protected function getPositionsByOrderId($id){
		$sql = "SELECT d.*,t.tw_Nazwa, t.tw_Symbol FROM vwDokumenty as d
  			INNER  JOIN vwTowarLista as t ON tw_id = ob_towid
			WHERE dok_Id = {$id}";		
		$data = MSSql::getInstance()->query($sql);
		return $data;
	}


	public function add(){	
		$this->customer = isset($this->orderDetail['customer'])?$this->orderDetail['customer']:false;
		if(!$this->customer){
			throw new Exception('Brak danych "customer" dla zamówienia!',1);
		}
		if(!$this->products){
			throw new Exception('Brak danych "products" dla zamówienia!',1);
		}

		$this->orderGt = $this->subiektGt->SuDokumentyManager->DodajZK();		

		$customer = new Customer($this->subiektGt,$this->customer);
		if(!$customer->isExists()){
			$customer->add();
		}else{
			$customer->update();
		}
		
		$cust_data = $customer->get();
		$this->orderGt->KontrahentId = $cust_data['gt_id'];		
		
		foreach($this->products as $p){
			$add_postition = false;
			if(!($add_postition = $this->addPosition($p))
				&& $this->create_product_if_not_exists == false){					
					throw new Exception('Nie odnaleziono towaru o podanym kodzie: '.$p['code'],1);												
			}
			if(!$add_postition && $this->create_product_if_not_exists == true){
				$p_obj = new Product($this->subiektGt,$p);
				$p_obj->add();
				Logger::getInstance()->log('api','Utworzono produkt'.$p['code'],__CLASS__.'->'.__FUNCTION__,__LINE__);
				$this->addPosition($p);				
			}
		}

		$this->setGtObject();		
		$this->orderGt->Przelicz();
		$this->orderGt->Zapisz();	
		return array(
			'order_ref' => $this->orderGt->NumerPelny
		);
	}

	public function update(){
		return true;
	}

	public function getGt(){
		return $this->orderGt;	
	}
}
?>
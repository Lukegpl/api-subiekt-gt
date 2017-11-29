<?php
namespace APISubiektGT\SubiektGT;
use COM;
use Exception;
use APISubiektGT\SubiektGT\SubiektObj;
use APISubiektGT\Logger;
use APISubiektGT\SubiektGT\Product;
use APISubiektGT\SubiektGT\Customer;
use APISubiektGT\SubiektGT as SubiektGT;

class Order extends SubiektObj {
	protected $orderGt;
	protected $products = false;	
	protected $reference;
	protected $comments;
	protected $customer = false;
	protected $reservation = true;
	protected $order_ref = '';
	protected $amount = 0;
	protected $pay_type = 'transfer';
	protected $create_product_if_not_exists = false;
	protected $orderDetail= array();

	public function __construct($subiektGt,$orderDetail = array()){
		parent::__construct($subiektGt, $orderDetail);
		$this->excludeAttr('orderGt');
		$this->excludeAttr('orderDetail');

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
		
	}

	public function getPdf(){

	}

	protected function getGtObject(){
		$this->reference =  $this->orderGt->Tytul;
		$this->comments = $this->orderGt->Uwagi;
		$this->order_ref = $this->orderGt->NumerPelny;
		$this->reservation = $this->orderGt->Rezerwacja;		
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
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
	protected $selling_doc = '';
	protected $amount = 0;
	protected $paid_amount = 0;	
	protected $state = -1;
	protected $date_of_delivery = '';
	protected $payment_comments = '';
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
		//var_dump($p_data);
		$position = $this->orderGt->Pozycje->Dodaj($p_data['code']);
		$position->IloscJm = $product['qty'];
		$position->WartoscBruttoPrzedRabatem = floatval($product['price_before_discount']) * intval($product['qty']);
		$position->WartoscBruttoPoRabacie  = floatval($product['price']) * intval($product['qty']);	
		return $position;
	}

	protected function setGtObject(){
		$this->orderGt->Tytul = $this->reference;
		$this->orderGt->Uwagi  = $this->comments;	
		$this->orderGt->Rezerwacja = $this->reservation;		
		$this->orderGt->NumerOryginalny = $this->reference;
		switch($this->pay_type){
			case 'transfer' : $this->orderGt->PlatnoscPrzelewKwota = floatval($this->amount); break;
			case 'cart' : $this->orderGt->PlatnoscKartaKwota = floatval($this->amount); break;
			case 'money' : $this->orderGt->PlatnoscGotowkaKwota = floatval($this->amount); break;
			case 'credit' : $this->orderGt->PlatnoscKredytKwota = floatval($this->amount); break;
		}

	}

	public function getPdf(){
		$temp_dir = sys_get_temp_dir();
		if($this->is_exists){
			$file_name = $temp_dir.'/'.$this->gt_id.'.pdf';
			$this->orderGt->DrukujDoPliku($file_name,0);
			$pdf_file = file_get_contents($file_name);
			Logger::getInstance()->log('api','Wygenerowano pdf dokumentu: '.$this->order_ref ,__CLASS__.'->'.__FUNCTION__,__LINE__);
			return array('encoding'=>'base64','order_ref'=>$this->order_ref ,'pdf_file'=>base64_encode($pdf_file));
		}
		return false;
	}

	public function makeSaleDoc(){
		if(!$this->is_exists){
			throw new Exception('Nie odnaleziono dokumentu: '.$this->order_ref);
		}
		
		if($this->customer['is_company'] == true){
			$selling_doc = $this->subiektGt->SuDokumentyManager->DodajFS();
		}else{
			$selling_doc = $this->subiektGt->SuDokumentyManager->DodajPAi();
		}
		if($this->orderGt->WartoscBrutto == 0){
			throw new Exception('Nie można utworzyć dokumentu sprzedaży. 0 wartość dokumentu.');
		}

		try{
			$selling_doc->NaPodstawie(intval($this->gt_id));
		}catch(Exception $e){			
			throw new Exception('Nie można utworzyć dokumentu sprzedaży. '.$this->toUtf8($e->getMessage()));
		}
		try{
			$selling_doc->ZapiszSymulacja();
		}catch(Exception $e){
			if($selling_doc->PozycjeBrakujace->Liczba()){
				throw new Exception('Nie można utworzyć dokumentu sprzedaży. Brakuje produktów na magazynie!');
			}
		}
		if($this->customer['is_company']== false){
			$selling_doc->RejestrujNaUF = true;
		}
		$selling_doc->Podtytul = $this->orderGt->Tytul;//.'/'.$this->orderGt->order_ref;
		$selling_doc->Zapisz();			
		Logger::getInstance()->log('api','Utworzono dokument sprzedaży: '.$selling_doc->NumerPelny,__CLASS__.'->'.__FUNCTION__,__LINE__);
		return array(
			'doc_ref' => $selling_doc->NumerPelny
		);
		
	}

	protected function getGtObject(){	
		if(!$this->orderGt){
			return false;
		}
		$this->gt_id = $this->orderGt->Identyfikator;
		$o = $this->getOrderById($this->gt_id);
		
		$this->reference =  $o['dok_NrPelnyOryg'];
		$this->selling_doc = $o['pow_NrPelny'];
		$this->comments = $o['dok_Uwagi'];
		$this->order_ref = $o['dok_NrPelny'];
		$this->reservation = $o['statusrez'];	
		$this->state = $o['dok_Status'];				
		$this->amount = $o['dok_WartBrutto'];
		$this->date_of_delivery = $o['dok_TerminRealizacji'];
		
		$customer = Customer::getCustomerById($this->orderGt->KontrahentId);		
		$this->customer = $customer;

		$positions = array();
		for($i=1; $i<=$this->orderGt->Pozycje->Liczba(); $i++){
			$positions[$this->orderGt->Pozycje->Element($i)->Id]['name'] = $this->orderGt->Pozycje->Element($i)->TowarNazwa;
			$positions[$this->orderGt->Pozycje->Element($i)->Id]['code'] = $this->orderGt->Pozycje->Element($i)->TowarSymbol;
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

	protected function getOrderById($id){
		$sql = "SELECT * FROM vwDok4ZamGrid WHERE dok_Id = {$id}";		
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
		}
		
		$cust_data = $customer->get();		
		$this->orderGt->KontrahentId = intval($cust_data['gt_id']);	
		
		foreach($this->products as $p){
			$add_postition = false;
			if(!($add_postition = $this->addPosition($p))
				&& $this->create_product_if_not_exists == false){					
					throw new Exception('Nie odnaleziono towaru o podanym kodzie: '.$p['code'],1);												
			}
			if(!$add_postition && $this->create_product_if_not_exists == true){
				$p_obj = new Product($this->subiektGt,$p);
				$p_obj->add();				
				$this->addPosition($p);				
			}
		}

		$this->setGtObject();			
		$this->orderGt->Przelicz();
		$this->orderGt->Zapisz();
		Logger::getInstance()->log('api','Utworzono zamówienie od klienta: '.$this->orderGt->NumerPelny,__CLASS__.'->'.__FUNCTION__,__LINE__);	
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
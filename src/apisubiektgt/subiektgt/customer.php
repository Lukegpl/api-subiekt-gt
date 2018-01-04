<?php
namespace APISubiektGT\SubiektGT;

use COM;
use APISubiektGT\Logger;
use APISubiektGT\MSSql;
use APISubiektGT\SubiektGT\SubiektObj;


class Customer extends SubiektObj{	
	protected $customerGt = false;	
	protected $email;
	protected $ref_id = false;
	protected $firstname;
	protected $lastname;
	protected $post_code;
	protected $city;
	protected $tax_id = '';
	protected $company_name ='';
	protected $address;
	protected $address_no;
	protected $phone = false;
	protected $is_company = false;
	


	public function __construct($subiektGt,$customerDetail = array()){						
		parent::__construct($subiektGt, $customerDetail);
		$this->excludeAttr('customerGt');

		if($this->ref_id && $subiektGt->Kontrahenci->Istnieje($this->ref_id)){
			$this->customerGt = $subiektGt->Kontrahenci->Wczytaj($this->ref_id);
			$this->getGtObject();
			$this->is_exists = true;				
		}		
		
		if(!$this->customerGt && $this->is_company && $subiektGt->Kontrahenci->Istnieje($this->tax_id)){
			$this->customerGt = $subiektGt->Kontrahenci->Wczytaj($this->tax_id);
			$this->getGtObject();
			$this->is_exists = true;				
		}			
	}

	protected function setGtObject(){			
		$this->customerGt->Symbol = $this->ref_id;		
		if($this->is_company){			
			$this->customerGt->NazwaPelna = $this->company_name;
			$this->customerGt->Nazwa = substr($this->company_name,0,50);
			$this->customerGt->Osoba = 0;
			$this->customerGt->NIP =  sprintf('%s',$this->tax_id);
			$this->customerGt->Symbol = $this->customerGt->NIP;	

		}else{
			$this->customerGt->Osoba = 1;
			$this->customerGt->OsobaImie = $this->firstname;			
			$this->customerGt->OsobaNazwisko = $this->lastname;
			$this->customerGt->NazwaPelna = $this->firstname.' '.$this->lastname;
		}		
		$this->customerGt->Email = $this->email;
		$this->customerGt->Miejscowosc = $this->city;
		$this->customerGt->KodPocztowy = $this->post_code;
		$this->customerGt->Ulica = $this->address;
		$this->customerGt->NrDomu = substr($this->address_no,0,7);

		if($this->phone){
			if($this->customerGt->Telefony->Liczba==0){
				$phoneGt = $this->customerGt->Telefony->Dodaj($this->phone);	
			}else{
				$phoneGt = $this->customerGt->Telefony->Element(1);

			}
			$phoneGt->Nazwa = 'Primary';
			$phoneGt->Numer = $this->phone;
			$phoneGt->Typ = 3;
		}
		return true;
	}

	protected function getGtObject(){	
		$this->is_company = !$this->customerGt->Osoba;
		$this->gt_id = $this->customerGt->Identyfikator;
		$this->ref_id = $this->customerGt->Symbol;		
		$this->company_name = $this->customerGt->NazwaPelna;
		$this->tax_id = $this->customerGt->NIP;
		$this->firstname = $this->customerGt->OsobaImie;		
		$this->lastname = $this->customerGt->OsobaNazwisko;						
		$this->email = $this->customerGt->Email;
		$this->city = $this->customerGt->Miejscowosc;
		$this->post_code = $this->customerGt->KodPocztowy;
		$this->address = $this->customerGt->Ulica;
		$this->address_no =$this->customerGt->NrDomu;
		
		if($this->customerGt->Telefony->Liczba>0){
			$phoneGt = $this->customerGt->Telefony->Element(1);
			$this->phone = $phoneGt->Numer;
		}	
		return true;							
	}

	static public function getCustomerById($id){
		$sql = "SELECT * FROM vwKlienci WHERE kh_Id = {$id}";		
		$data = MSSql::getInstance()->query($sql);
		if(!isset($data[0])){
			return false;
		}
		$data = $data[0];		
		$ret_data  = array(
			'ref_id' => $data['kh_Symbol'],
			'company_name' => $data['Firma'],
			'tax_id' => $data['adr_NIP'],
			'fullname' => $data['adr_NazwaPelna'],			
			'email' => $data['kh_EMail'],
			'city' => $data['adr_Miejscowosc'],
			'post_code' => $data['adr_Kod'],
			'address' => $data['adr_Adres'],			
			'phone' => $data['adr_Telefon'],
			'is_company' => $data['kh_Typ']==2?false:true,
		);
		return $ret_data;
	}

	public function add(){
		$this->customerGt = $this->subiektGt->Kontrahenci->Dodaj();
		$this->setGtObject();		
		$this->customerGt->Zapisz();		
		Logger::getInstance()->log('api','Utworzono klienta od klienta: '.$this->customerGt->Symbol,__CLASS__.'->'.__FUNCTION__,__LINE__);
		$this->gt_id = $this->customerGt->Identyfikator;		
		return array('gt_id'=>$this->customerGt->Identyfikator);
	}

	public function update() {
		if(!$this->customerGt){
			return false;
		}
		$this->setGtObject();				
		$this->customerGt->Zapisz();			
		Logger::getInstance()->log('api','Zaktualizowano klienta od klienta: '.$this->customerGt->Symbol,__CLASS__.'->'.__FUNCTION__,__LINE__);
		return true;
	}

	public function getGt(){
		return $this->customerGt;
	}

}
?>
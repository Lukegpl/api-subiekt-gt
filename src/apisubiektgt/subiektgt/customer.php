<?php
namespace APISubiektGT\SubiektGT;
use COM;

class Customer {	
	protected $customerGt = false;
	protected $is_exists = false;
	protected $subiektGt;
	protected $email;
	protected $ref_id;
	protected $firstname;
	protected $lastname;
	protected $post_code;
	protected $city;
	protected $tax_id;
	protected $company_name;
	protected $address;
	protected $address_no;
	protected $phone = false;
	protected $is_company = false;
	protected $exclude = array('customerGt','subiektGt','exclude');


	public function __construct($subiektGt,$customerDetail = array()){				
		foreach($customerDetail as $key=>$value){
			$this->{$key} = mb_convert_encoding($value,'ISO-8859-2');
		}

		if(isset($customerDetail['ref_id'])){
			$symbol = trim($customerDetail['ref_id']);
		}
		if($subiektGt->Kontrahenci->Istnieje($symbol)){
			$this->customerGt = $subiektGt->Kontrahenci->Wczytaj($symbol);
			$this->getGtObject();
			$this->is_exists = true;			
		}
		$this->subiektGt = $subiektGt;		
	}

	protected function setGtObject(){
		$this->customerGt->Symbol = $this->ref_id;
		if($this->is_company){			
			$this->customerGt->NazwaPelna = $this->company_name;
			$this->customerGt->Nazwa = substr($this->company_name,0,100);
			$this->customerGt->Osoba = 0;
			$this->customerGt->NIP =  $this->tax_id;

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
		$this->customerGt->NrDomu = $this->address_no;

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
	}

	protected function getGtObject(){
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
	}

	public function isExists(){
		return $this->is_exists;
	}

	public function add(){
		$this->customerGt = $this->subiektGt->Kontrahenci->Dodaj();
		$this->setGtObject();		
		$this->customerGt->Zapisz();
	}

	public function update() {
		$this->setGtObject();
		$this->customerGt->Zapisz();	
	}

	public function getGt(){
		return $this->customerGt;
	}

	public function get(){
		$ret_data = array();
		foreach ($this as $key => $value) {
			if(in_array($key,$this->exclude)){
				continue;
			}
			$ret_data[$key] = mb_convert_encoding($value,'UTF-8','ISO-8859-2');
		}
		return $ret_data;
	}

}
?>
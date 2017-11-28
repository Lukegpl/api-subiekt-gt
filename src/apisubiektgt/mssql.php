<?php
namespace APISubiektGT;
use Exception;

class MSSql{
	
	static private $instance = false;
	private $conf = array();
	private $conn = false;
	
	
	private function __construct($conf = array(),$data_base){ 
		$this->conn = sqlsrv_connect( $data_base, $conf);
		if( $this->conn === false )
		{		     
			$errors = sqlsrv_errors();
		     throw new Exception ("Could not connect. ".$errors[0]['message']);		     
		}
	}
	
	static public function getInstance($conf = array(),$data_base = ''){
		if(false == self::$instance){
			self::$instance = new MSSql($conf,$data_base);
		}
		return self::$instance;
	}
	
	public function query($query){
		$data = sqlsrv_query($this->conn ,$query);
		$result = array();   
	
		if($data == false){
			die( print_r( sqlsrv_errors(), true));
		}
		
		while($row = sqlsrv_fetch_array( $data, SQLSRV_FETCH_ASSOC)){
			$result[] = $row;
		}
		
		return $result;
	}	
	
	public function exec($dml,$params = array()){
		$stmt = sqlsrv_query( $this->conn, $dml, $params);
		if( $stmt == false){
		     die( print_r( sqlsrv_errors(), true));
		}
		sqlsrv_free_stmt( $stmt);		
	}
		
	public function __destruct(){
		sqlsrv_close($this->conn);	
	}
	
}
?>
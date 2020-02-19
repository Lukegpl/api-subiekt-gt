<?php

namespace APISubiektGT;

use APISubiektGT\Config;
use APISubiektGT\Logger;
use APISubiektGT\MSSql;
use COM;

class SubiektGT
{
	static protected $_instance;
	protected $cfg;
	protected $subiektGt;
	protected $api_key = false;

	public function __construct(Config $cfg)
	{
		$this->cfg = $cfg;
		$this->api_key = $cfg->getAPIKey();
	}


	static public function getInstance(Config $cfg = null)
	{
		if (!self::$_instance) {
			self::$_instance = new SubiektGT($cfg);
		}
		return self::$_instance;
	}


	/**
	 *	create com Object and conncet to database.
	 */
	public function connect()
	{
		$mssqlConnectionInfo = array(
			"UID" => $this->cfg->getDbUser(),
			"PWD" => $this->cfg->getDbUserPass(),
			"Database" => $this->cfg->getDatabase()
		);
		MSSql::getInstance($mssqlConnectionInfo, $this->cfg->getServer());

		$gt = new COM("InsERT.GT") or die("Cannot create an InsERT GT object");
		$gtD = new COM("InsERT.Dodatki") or die("Cannot create an Insert Dodatki object");

		$gt->Produkt = 1;
		$gt->Autentykacja = 0;
		$gt->Serwer = $this->cfg->getServer();
		$gt->Uzytkownik = $this->cfg->getDbUser();
		$gt->UzytkownikHaslo = $gtD->Szyfruj($this->cfg->getDbUserPass());
		$gt->Baza = $this->cfg->getDatabase();
		$gt->Operator = $this->cfg->getOperator();
		$gt->OperatorHaslo = $gtD->Szyfruj($this->cfg->getOperatorPass());
		$this->subiektGt = $gt->Uruchom(0, 4);

		$this->subiektGt->MagazynId = intval($this->cfg->getWarehouse());
		return $this->subiektGt;
	}

	/**
	 *	Return config object
	 */
	public function getConfig()
	{
		return $this->cfg;
	}
}

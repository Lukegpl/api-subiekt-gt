<?php
use APISubiektGT\Config;
use APISubiektGT\SubiektGT;

	require_once(dirname(__FILE__).'/../init.php');
	$cfg = new Config(CONFIG_INI_FILE);
	//$cfg->load();
	SubiektGT::getInstance($cfg)->connect();
	
?>
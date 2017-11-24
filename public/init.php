<?php	
use APISubiektGT\Logger;

DEFINE('CONFIG_INI_FILE',dirname(__FILE__).'/../config/api-subiekt-gt.ini');
DEFINE('LOG_DIR',dirname(__FILE__).'/../log/');	

include_once(dirname(__FILE__).'/../src/autoload.php');
Logger::getInstance(LOG_DIR);


?>
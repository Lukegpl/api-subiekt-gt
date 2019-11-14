<?php
use APISubiektGT\Config;
use APISubiektGT\Helper;

require_once(dirname(__FILE__).'/../init.php');

$cfg = new Config(CONFIG_INI_FILE);
$cfg->load();

$api_url = $_SERVER['SERVER_NAME'].str_replace('setup/test.php','api/document/get',$_SERVER['REQUEST_URI']);
if(Helper::getIsset('testdoc')){
	// The data to send to the API
	$postData = array(
	    'api_key' => $cfg->getAPIKey(),
	    'data'=>array('doc_ref'=>$_POST['testdoc'])
	);

	$json_request  = json_encode($postData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	// Setup cURL
	$ch = curl_init($api_url);
	curl_setopt_array($ch, array(
	    CURLOPT_POST => TRUE,
	    CURLOPT_RETURNTRANSFER => TRUE,
	    CURLOPT_HTTPHEADER => array(
	        //'Authorization: '.$authToken,
	        'Content-Type: application/json' 
	    ),
	    CURLOPT_POSTFIELDS => $json_request
	));

	// Send the request
	$response = curl_exec($ch);

	// Check for errors
	if($response === FALSE){
	    die(curl_error($ch));
	}
}

// Decode the response
//$responseData = json_decode($response, TRUE);
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>TEST SubiektGT + Sfera API</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
    
</head>
<body>
Wysłane żądanie:	
<pre>
<code>
<?php echo isset($json_request)?$api_url:'' ?>

<?php  echo isset($json_request)?$json_request:'';?>
</code>
</pre>
Odebrana odpowiedź:
<pre><code>
<?php 	
	echo isset($response)?$response:'';
?>
</code></pre>
</body>
</html>
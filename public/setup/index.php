<?php
use APISubiektGT\Config;

	require_once(dirname(__FILE__).'/../init.php');
	$cfg = new Config(CONFIG_INI_FILE);
	try{
		$cfg->load();
	}catch(Exception $e){

	}
	$exts  = get_loaded_extensions();

	$need_ex = array('com_dotnet' =>false, 'sqlsrv' =>false);
	foreach($exts as $ex){
		switch($ex){
			case 'com_dotnet': $need_ex[$ex] = true; break;
			case 'sqlsrv': $need_ex[$ex] = true; break;
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>SubiektGT + Sfera API Setup</title>
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

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container">
    <div class="row">
      <div class="twleve column" style="margin-top: 5%">
        <h4>Konfiguracja API</h4>
        <h5>Sprawdzenie wymaganych bibliotek</h5>
	   <?php
	   	foreach($need_ex as $ex => $is){
	   		$color = '#FF2F01';
	   		$icon = 'fa-minus-circle';
	   		if($is == true){
	   			$color = '#00A474';
	   			$icon = 'fa-check-circle-o';
	   		}
	   		echo "<p style=\"color:$color;\"><i class=\"fa $icon\" aria-hidden=\"true\"></i> $ex</p>";
	   	}	   	
	   ?>
	   <p>Czy na komuterze jest zainstalowany SubiektGT ?</p>
    </div>
	</div>
    <div class="row">
      <div class="twleve column">          
	   <h5>Konfiguracja bazy danych</h5>
	   <p>Przeprowadź konfigurację umożliwiająca połączenie API ze Sferą SubiektaGT oraz MSSql Serverem.</p>        
      </div>
    </div>
    <div class="row">
      <div class="one-half column">
     		<label for="server">IP/Nazwa serwera z bazą MSSql</label>
          <input class="u-full-width" id="server" type="text" placeholder="192.168.0.1\sqlserver,1433">
      </div>
      <div class="one-half column">
     		<label for="dbuser">Użytkownik bazy danych dla podmiotu</label>
          <input class="u-full-width" id="dbuser" type="text" placeholder="sa">
      </div> 
  </div>
  <div class="row">
  	 <div class="one-half column">
     		<label for="dbpass">Hasło użytkownika bazy danych</label>
          <input class="u-full-width" id="dbpass" type="text" placeholder="pa55w0rd">
      </div>
      <div class="one-half column">
     		<label for="database">Baza danych/podmiot subiektaGT</label>
          <input class="u-full-width" id="database" type="text" placeholder="MOJAFIRMA">
      </div>  
  </div>
  <div class="row">
  	 <div class="one-half column">
     		<label for="newprefix">Prefix dla nowych produktów dodawanych do subiekta</label>
          <input class="u-full-width" id="newprefix" type="text" placeholder="**NEW**" value="">
      </div>
      <div class="one-half column">
     		<label for="database">Twój klucz API</label>
          <input class="u-full-width" id="database" type="text" disabled=="true">
      </div>  
  </div>  
  <div class="row">
  	  <div class="one-half column">
     		<input class="button-primary" type="submit" value="Zapisz konfigurację">
      </div>  
  </div>
    <div class="row">
      <div class="twleve column">          
	   <h5>Sprawdź konfigurację</h5>
	   <p>Sprawdź połączenie z bazą danych oraz czy jest dostęp do obiektów SferyGT</p>        
      </div>
    </div>


</div>
<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>


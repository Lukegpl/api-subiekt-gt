<?php

use APISubiektGT\Config;
use APISubiektGT\Helper;

require_once(dirname(__FILE__) . '/../init.php');

$cfg_values = array(
	'server' => '',
	'dbuser' => '',
	'dbpassword' => '',
	'database' => '',
	'new_product_prefix' => '',
	'api_key' => '',
	'operator' => '',
	'operator_password' => '',
	'id_default_attribute' => ''
);

$need_ex = array(
	'com_dotnet' => false,
	'sqlsrv' => false
);

//Config object	
$cfg = new Config(CONFIG_INI_FILE);
$cfg->load();

//Save new Configuration
if (Helper::getIsset('save')) {
	$cfg->setServer(Helper::getValue('server'));
	$cfg->setDbUser(Helper::getValue('dbuser'));
	$cfg->setDbUserPass(Helper::getValue('dbpass'));
	$cfg->setDatabase(Helper::getValue('database'));
	$cfg->setNewProductPrefix(Helper::getValue('newprefix'));
	$cfg->setIdPerson(Helper::getValue('id_person'));
	$cfg->setOperator(Helper::getValue('operator'));
	$cfg->setOperatorPass(Helper::getValue('operator_password'));
	$cfg->setWarehouse(Helper::getValue('id_warehouse'));
	$cfg->setDefaultAttribute(Helper::getValue('id_default_attribute'));
	$cfg->save();
}

//Save new Api Key
if (Helper::getIsset('new_api_key')) {
	$cfg->newAPIKey();
	$cfg->save();
}

try {

	$cfg_values['server'] = $cfg->getServer();
	$cfg_values['dbuser'] = $cfg->getDbUser();
	$cfg_values['dbpassword'] = $cfg->getDbUserPass();
	$cfg_values['database'] = $cfg->getDatabase();
	$cfg_values['new_product_prefix'] = $cfg->getNewProductPrefix();
	$cfg_values['id_person'] = $cfg->getIdPerson();
	$cfg_values['operator'] = $cfg->getOperator();
	$cfg_values['operator_password'] = $cfg->getOperatorPass();
	$cfg_values['id_warehouse'] = $cfg->getWarehouse();
	$cfg_values['id_default_attribute'] = $cfg->getDefaultAttribute();
	if (strlen($cfg->getAPIKey()) == 0) {
		$cfg->newAPIKey();
		$cfg->save();
	}
	$cfg_values['api_key'] = $cfg->getAPIKey();;
} catch (Exception $e) { }

//Get extensions from PHP and verify with needed to run api
$exts  = get_loaded_extensions();
foreach ($exts as $ex) {
	switch ($ex) {
		case 'com_dotnet':
			$need_ex[$ex] = true;
			break;
		case 'sqlsrv':
			$need_ex[$ex] = true;
			break;
	}
}
?>
	<!DOCTYPE html>
	<html lang="pl">

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
					<h5>Sprawdzenie wymagań</h5>
					<?php
					foreach ($need_ex as $ex => $is) {
						$color = '#FF2F01';
						$icon = 'fa-minus-circle';
						if ($is == true) {
							$color = '#00A474';
							$icon = 'fa-check-circle-o';
						}
						echo "<span style=\"color:$color;\"><i class=\"fa $icon\" aria-hidden=\"true\"></i> biblioteka php: $ex</span><br/>";
					}
					?>

					<?php
					$cfg_dir = dirname(CONFIG_INI_FILE);
					if (is_dir($cfg_dir)) {
						echo "<span style=\"color:#00A474;\"><i class=\"fa fa-check-circle-o\" aria-hidden=\"true\"></i> katalog konfiguracyjny </span><br/>";
					} else {
						echo "<span style=\"color:#FF2F01;\"><i class=\"fa fa-minus-circle\" aria-hidden=\"true\"></i> brak katalogu konfiguracyjnego $cfg_dir</span><br/>";
					}


					if (is_dir(LOG_DIR)) {
						echo "<span style=\"color:#00A474;\"><i class=\"fa fa-check-circle-o\" aria-hidden=\"true\"></i> katalog logów</span><br/>";
					} else {
						echo "<span style=\"color:#FF2F01;\"><i class=\"fa fa-minus-circle\" aria-hidden=\"true\"></i> brak katalogu logów:  " . CONFIG_INI_FILE . "</span><br/>";
					}

					if (is_writable($cfg_dir)) {
						echo "<span style=\"color:#00A474;\"><i class=\"fa fa-check-circle-o\" aria-hidden=\"true\"></i> prawa zapisu do katalogu konfiguracyjnego </span><br/>";
					} else {
						echo "<span style=\"color:#FF2F01;\"><i class=\"fa fa-minus-circle\" aria-hidden=\"true\"></i> brak praw zapisu do katalogu konfiguracyjnego $cfg_dir</span><br/>";
					}

					if (file_exists(CONFIG_INI_FILE)) {
						echo "<span style=\"color:#00A474;\"><i class=\"fa fa-check-circle-o\" aria-hidden=\"true\"></i> plik konfiguracyjny </span><br/>";
					} else {
						echo "<span style=\"color:#FF2F01;\"><i class=\"fa fa-minus-circle\" aria-hidden=\"true\"></i> brak pliku konfiguracyjnego " . CONFIG_INI_FILE . "</span><br/>";
					}
					?>
				</div>
			</div>
			<form method="post">
				<div class="row">
					<div class="twleve columns">
						<h5>Konfiguracja bazy danych</h5>
						<p>Przeprowadź konfigurację umożliwiająca połączenie API ze Sferą SubiektaGT oraz MSSql Serverem.</p>
					</div>
				</div>
				<div class="row">
					<div class="one-half column">
						<label for="server">IP/Nazwa serwera z bazą MSSql</label>
						<input class="u-full-width" name="server" type="text" placeholder="192.168.0.1\sqlserver,1433" value="<?php echo  $cfg_values['server']; ?>">
					</div>
					<div class="one-half column">
						<label for="dbuser">Użytkownik bazy danych dla podmiotu</label>
						<input class="u-full-width" name="dbuser" type="text" placeholder="sa" value="<?php echo  $cfg_values['dbuser']; ?>">
					</div>
				</div>
				<div class="row">
					<div class="one-half column">
						<label for="dbpass">Hasło użytkownika bazy danych</label>
						<input class="u-full-width" name="dbpass" type="text" placeholder="pa55w0rd" value="<?php echo  $cfg_values['dbpassword']; ?>">
					</div>
					<div class="one-half column">
						<label for="database">Baza danych/podmiot subiektaGT</label>
						<input class="u-full-width" name="database" type="text" placeholder="MOJAFIRMA" value="<?php echo  $cfg_values['database']; ?>">
					</div>
				</div>
				<div class="row">
					<div class="one-half column">
						<label for="newprefix">Prefix dla nowych produktów dodawanych do subiekta</label>
						<input class="u-full-width" name="newprefix" type="text" placeholder="**NEW**" value="<?php echo  $cfg_values['new_product_prefix']; ?>">
					</div>
					<div class="one-half column">
						<label for="database">Twój klucz API</label>
						<input class="u-full-width" id="database" type="text" disabled="true" value="<?php echo  $cfg_values['api_key']; ?>">
					</div>
				</div>
				<div class="row">
					<div class="one-half column">
						<label for="newprefix">Domyślne dane osoby wystawiającego dokumenty</label>
						<input class="u-full-width" name="id_person" type="text" placeholder="Jan Kowalski" value="<?php echo  $cfg_values['id_person']; ?>">
					</div>
					<div class="one-half column">
						<label for="dbpass">Id domyślnej cechy dla nowego produktu</label>
						<input class="u-full-width" name="id_default_attribute" type="text" placeholder="1" value="<?php echo  $cfg_values['id_default_attribute']; ?>">				</div>
				</div>
				<div class="row">
					<div class="one-half column">
						<label for="dbpass">Operator (Administracja->Słownik->Personel)</label>
						<input class="u-full-width" name="operator" type="text" placeholder="Szef" value="<?php echo  $cfg_values['operator']; ?>">
					</div>
					<div class="one-half column">
						<label for="database">Hasło operatora</label>
						<input class="u-full-width" name="operator_password" type="text" placeholder="123456" value="<?php echo  $cfg_values['operator_password']; ?>">
					</div>
				</div>
				<div class="row">
					<div class="one-half column">
						<label for="dbpass">Id Magazynu domyślnego</label>
						<input class="u-full-width" name="id_warehouse" type="text" placeholder="1" value="<?php echo  $cfg_values['id_warehouse']; ?>">
					</div>
				</div>				
				<div class="row">
					<div class="one-half column">
						<input class="button-primary" type="submit" value="Zapisz konfigurację" name="save">
						<input class="button-primary" type="submit" value="Generuj nowy API KEY" name="new_api_key">
					</div>
				</div>
			</form>
			<form method="post" target="testframe" action="test.php">
				<div class="row">
					<div class="twleve columns">
						<h5>Sprawdź konfigurację</h5>
						<p>Sprawdź połączenie z bazą danych oraz czy jest dostęp do obiektów SferyGT</p>
					</div>
				</div>
				<div class="row">
					<div class="three columns">
						<label for="server">Dokument do pobrania</label>
						<input class="u-full-width" name="testdoc" type="text" placeholder="PA 11364/12/2017">
					</div>
				</div>
				<div class="row">
					<div class="twleve columns">
						<input class="button-primary" type="submit" value="Testuj połączenie" name="connect">
					</div>
				</div>
			</form>
			<div class="row">
				<div class="twlevecolumn columns">
					<iframe name="testframe" frameborder="0" style="width:100%;height:500px;"></iframe>
				</div>
			</div>
		</div>
		<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
	</body>

	</html>
<?php
	if(!IN_SYSTEM) exit;

	define('ROOT_DIR', dirname(__FILE__));
	require(ROOT_DIR . '/config.php');
	require(ROOT_DIR . '/build.php');

	foreach(glob(ROOT_DIR . '/libraries/*.php') as $lib)
		require_once($lib);

	error_reporting(E_ALL);
	$dbg = new debug();
	//set_error_handler('', E_ALL | E_STRICT);

	try
	{
		$db = new PDO("mysql:host={$config['db_host']};dbname={$config['db_database']};charset=utf8", $config['db_username'], $config['db_password']);
	}
	catch(PDOException $e)
	{
		$dbg->pdo_handler($e);
	}

	$sys = new sys($settings);
	$sec = new security();
	$session = new session();
	$lang = new language($settings['sys_language']);
	foreach(explode(',', $settings['sys_extensions']) as $e)
	{
		$ext[$e] = new extension($e, $lang->lang_phr);
		if(!isset($ext[$e]->extension)) unset($ext[$e]);
	}

	date_default_timezone_set($settings['time_timezone']);
?>

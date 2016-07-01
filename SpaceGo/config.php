<?php
	if(!IN_SYSTEM) exit;

	$config = array(
		// database
		'db_host'		=>	'localhost',
		'db_username'	=>	'root',
		'db_password'	=>	'',
		'db_database'	=>	'cms',

		// locale
		'loc_dir'		=> 'SpaceGo/SpaceGo',
		'loc_os'		=> 1
	);

	$env = array(
		'nl' => $config['loc_os'] ? "\r\n" : "\n"
	);
?>
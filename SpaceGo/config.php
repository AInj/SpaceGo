<?php
	/*
	*
	* This file is part of SpaceGo <https://github.com/AInj/SpaceGo>
	*
	* Copyright (c) 2016
	* Released under The MIT License (MIT)
	* Refer to LICENSE file for full copyright and license information
	*
	*/

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
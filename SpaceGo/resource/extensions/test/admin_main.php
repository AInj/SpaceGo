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

	// ACP module: ext/test/main
	if(!IN_SYSTEM) exit;

	$out = 'Because I can!';

	// Push module
	$module = array(
		'nav'			=> ['Yes', 'fa fa-heart', ''],
		'name'			=> 'It basically works!',
		'permission'	=> '',
		'output'		=> $out
	);
?>
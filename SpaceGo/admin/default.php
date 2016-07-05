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

	// ACP module: default
	if(!IN_SYSTEM) exit;

	$out = $lang->get('acp_m_default_welcome');

	// Push module
	$module = array(
		'nav'			=> '',
		'name'			=> $lang->get('acp_m_default'),
		'permission'	=> '',
		'output'		=> $out
	);
?>
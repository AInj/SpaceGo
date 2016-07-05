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

	define('IN_SYSTEM', true);
	include('system.php'); // game on

	// $dbg->halt('Shutdown message');

	$theme = new theme($settings['sys_theme']);

	$test = 'Testing extension: test<br>
	Lang #1: ' . $lang->get('ext_test_test') . '<br>
	Lang #2 & theme dat: ' . $theme->dat('ext', 'test', 'tst', $lang->get('ext_test_hello', 'avidor')) . '<br>';

	$ext['test']->event('inj', 'uzi');

	$bind = array(
		'TITLE' => 'Title',
		'DESC' => 'desc test',
		'TEST' => $test,
		'MENUITEMS' => $ext['menu']->event('load', $ext['menu']->event('getID', 'Sidemenu'))
	);
	$theme->load('header', $bind);
	$theme->load('body', $bind);
	$theme->load('footer');

	$theme->execute();
?>

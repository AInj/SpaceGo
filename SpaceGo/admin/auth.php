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

	if(basename($_SERVER['PHP_SELF']) != "auth.php") header("location: index.php");

	define('IN_SYSTEM', true);
	include('../system.php');

	if(!isset($_POST['u']) || !isset($_POST['p']) || $sec->ban('auth')) exit;

	$query = $db->prepare('SELECT `id`, `username`, `password`, `connection` FROM `accounts` WHERE `username` = ?');
	$query->bindParam(1, $_POST['u'], PDO::PARAM_STR);
	$query->execute();
	$res = $query->fetch(PDO::FETCH_ASSOC);
	if($sec->pwdVerify($_POST['p'], $res['password']))
	{
		$ssid = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
		$session->set('admin_auth', $res['id']);
		$session->set('admin_auth_session', $ssid);
		$query = $db->prepare('UPDATE `accounts` SET `session` = ? WHERE `id` = ?');
		$query->execute([$ssid, $res['id']]);
		print('Welcome, ' . $res['username']);
	}
	else
	{
		$exchange = 'Invalid credentials. ';
		if($settings['sec_authbanl'] > -1)
		{
			if(!$session->get('admin_auth_invalid'))
				$session->set('admin_auth_invalid', 0);
			$session->set('admin_auth_invalid', $session->get('admin_auth_invalid') + 1);
			if($session->get('admin_auth_invalid') >= $settings['sec_authatts'])
			{
				$sec->banSet('auth', $settings['sec_authbanl'], 'Failed attempts');
				$session->set('admin_auth_invalid', 0);
				$exchange .= 'Try again later';
			}
			else $exchange .= ($settings['sec_authatts'] - $session->get('admin_auth_invalid')) . ' remaining attempts';
		}
		$session->set('admin_auth_exchange', $exchange);
	}
?>

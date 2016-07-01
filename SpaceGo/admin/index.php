<?php
	define('IN_SYSTEM', true);
	include('../system.php');

	// Admin ACL
	if($settings['sec_admacl'])
	{
		$allowed = 0;
		$query = $db->prepare('SELECT * FROM `admacl`');
		$query->execute();
		$res = $query->fetchAll();
		foreach($res as $row)
		{
			if((filter_var($row['host'], FILTER_VALIDATE_IP) ? $row['host'] : gethostbyname($row['host'])) == $_SERVER['REMOTE_ADDR'])
			{
				$allowed = 1;
				break;
			}
		}
		if(!$allowed) header("location: ../index.php");
	}

	// Authentication
	if(!$session->get('admin_auth'))
	{
		$theme = new theme('admin/_auth');
		$bind = array('TITLE' => $lang->get('acp'), 'LINE' => $session->get('admin_auth_exchange'));
		$theme->load('auth', $bind);
		$theme->execute();
		if($session->get('admin_auth_exchange'))
		{
			if($sec->ban('auth'))
				$session->set('admin_auth_exchange', 'Temporarily banned, try again later');
			else $session->rst('admin_auth_exchange');
		}
		exit;
	}

	// Fetch account data & update connection
	$auth = $sys->loadAcc($session->get('admin_auth'));
	if(empty($auth) || $session->get('admin_auth_session') != $auth['session'])
	{
		$session->destroy();
		header("location: index.php");
		exit;
	}
	$query = $db->prepare('UPDATE `accounts` SET `connection` = ? WHERE `id` = ?');
	$query->execute([time(), $auth['id']]);

	// Lock on theme
	$theme = new theme('admin');

	// Admin modules
	$_module = $module = !empty($_GET) ? array_keys($_GET)[0] : 'default';
	if(startsWith($module, $r = 'e_'))
	{
		$e = explode('_', substr($module, strlen($r)));
		if(isset($ext[$e[0]]) && file_exists(($inc = ROOT_DIR . "/resource/extensions/{$e[0]}/admin_{$e[1]}.php"))) include($inc);
	}
	elseif(file_exists(($inc = ROOT_DIR . "/admin/{$module}.php"))) include($inc);
	if($_module == $module) header('location: ?');

	// Permission
	if(!$sec->permission($module['permission']))
		$module['output'] = $theme->dat('acp', 'alert', 'danger', $lang->get('acp_noaccess'));

	// Messages
	$messages = '';
	$messages_unread = 0;
	$query = $db->prepare('SELECT * FROM `messages` WHERE `to` = ? ORDER BY `time` DESC LIMIT 2');
	$query->execute([$auth['id']]);
	$res = $query->fetchAll();
	foreach($res as $row)
	{
		if(!$row['read']) $messages_unread++;
		$messages .= $theme->dat('nav', 'list_messages', ((!$row['read'] ? '<i class="fa fa-envelope"></i><i> ' : '') . $row['subject'] . (!$row['read'] ? '</i>' : '')),
			$lang->get('acp_xbyx', date($settings['time_fs'], $row['time']), $row['sender']), truncate($row['content'], 30), $row['id']);
	}
	$messages_c = $messages_unread ? '#c94c4c' : '';

	// Navigation
	$nav = '';
	$nav_items = array(array($lang->get('acp_m_default'), $theme->dat('nav', 'acp_m_default'), '?'));
	if(!empty($module['nav']))
	{
		if(count($module['nav']) == count($module['nav'], COUNT_RECURSIVE)) array_push($nav_items, $module['nav']);
		else $nav_items = array_merge($nav_items, $module['nav']);
	}
	$nav_items_count = count($nav_items);
	foreach($nav_items as $c => $i)
	{
		$l = $c == $nav_items_count - 1;
		$nav .= $theme->dat('nav', 'nav_li', $i[1], !$l ? $theme->dat('nav', 'nav_li_a', $i[0], $i[2]) : $i[0], $l ? 'active' : '');
	}

	// Menu
	$menu = '';
	$menu_items = array(
		['default', 	$lang->get('acp_m_default'), 	$theme->dat('nav', 'acp_m_default'), 	''],
		['accounts', 	$lang->get('acp_m_accounts'), 	$theme->dat('nav', 'acp_m_accounts'), 	'acc'],
		['system',		$lang->get('acp_m_system'), 	$theme->dat('nav', 'acp_m_system'), 	'']
	);
	foreach($ext as $e) $e->acpMenu($menu_items);
	foreach($menu_items as $i) if($sec->permission($i[3]))
		$menu .= $theme->dat('nav', 'menu_li', $lang->parse($i[1]), $i[2], $i[0], $_module == $i[0] ? 'active' : '');

	// Handle theme
	$bind = array(
		// header
		'RTL'				=> $lang->lang_info['direction'] == 'rtl' ? $theme->dat('acp', 'rtl', $config['loc_dir'], $theme->theme) : '',
		'TITLE'				=> $lang->get('acp') . ' - ' . $module['name'],
		'ACP_TITLE'			=> $lang->get('acp'),

		// nav
		'USERNAME' 			=> $auth['username'],
		'MSGS_C'			=> $messages_c,
		'MSGS'				=> $messages,
		'HEADER'			=> $module['name'],
		'NAV'				=> $nav,
		'MENU'				=> $menu
	);

	$theme->load('header', $bind);
	$theme->load('nav', $bind);
	$theme->load('body', ['BODY' => $module['output']]);
	$theme->load('footer');

	$theme->execute();
?>

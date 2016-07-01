<?php
	// ACP module: logout
	if(!IN_SYSTEM) exit;

	$out = $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_logout_msg'));
	$session->destroy();
	header('refresh:3;url=?');

	// Push module
	$module = array(
		'nav'			=> array($lang->get('acp_m_logout'), $theme->dat('nav', 'acp_m_logout'), '?logout'),
		'name'			=> $lang->get('acp_m_logout'),
		'permission'	=> '',
		'output'		=> $out
	);
?>
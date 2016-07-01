<?php
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
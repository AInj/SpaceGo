<?php
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
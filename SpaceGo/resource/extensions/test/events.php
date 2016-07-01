<?php
	if(!IN_SYSTEM) exit;

	function ext_test_inj($var)
	{
		echo "works and " . $var;
	}

	function ext_test_loadMenu()
	{
		global $theme;
		$out = '';
		$items = array(
			[ 'Link 1', 'google.com', '' ],
			[ 'Link 2', 'google.co.il', 'active' ]
		);
		foreach($items as $item)
			$out .= $theme->dat('ext', 'test', 'menuitem', $item[0], $item[1], $item[2]);
		return $out;
	}
?>
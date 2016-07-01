<?php
	if(!IN_SYSTEM) exit;

	function ext_cm_getID($name)
	{
		global $db;
		$query = $db->prepare("SELECT `id` FROM `ext_menu` WHERE `name` = ?");
		$query->execute([$name]);
		return $query->fetchColumn();
	}

	function ext_cm_load($menu)
	{
		global $db, $theme;
		$query = $db->prepare('SELECT * FROM `ext_menu_items` WHERE `menu` = ? ORDER BY `sort` ASC');
		$query->execute([$menu]);
		$res = $query->fetchAll();
		$ret = '';
		foreach($res as $row)
			$ret .= $theme->dat('ext', 'menu', 'item', $row['item'], $row['link'], '');
		return $ret;
	}
?>

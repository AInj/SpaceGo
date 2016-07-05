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

	// ACP module: ext/menu/main
	if(!IN_SYSTEM) exit;

	$out = '';
	$nav = array(array($lang->get('ext_menu_acp'), 'fa fa-list-ul', '?e_menu_main'));

	if(!isset($_GET['cmd']))
	{
		$query = $db->prepare('SELECT * FROM `ext_menu`');
		$query->execute();
		$res = $query->fetchAll();
		$out .= '<div class="col-lg-12">
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-striped">
							<thread>
								<tr>
									<th>'.$lang->get('ext_menu_acp_id').'</th>
									<th>'.$lang->get('ext_menu_acp_name').'</th>
									<th>
										<center>
											<a href="?e_menu_main&cmd=create" class="btn btn-sm btn-success" role="button">'.$lang->get('ext_menu_acp_action_create').'</a>
										</center>
									</th>
								</tr>
							</thread>
							<tbody>';
		foreach($res as $row) 
		{
			$out .=	'			<tr>
									<td class="col-md-1">'.$row['id'].'</td>
									<td class="col-md-2">'.$row['name'].'</td>
									<td class="col-md-1" align="center">
										<a href="?e_menu_main&cmd='.$row['id'].'" class="btn btn-sm btn-primary" role="button">'.$lang->get('ext_menu_acp_action_modify').'</a>
										<a href="?e_menu_main&cmd=d_'.$row['id'].'" class="btn btn-sm btn-danger" role="button">'.$lang->get('ext_menu_acp_action_delete').'</a>
									</td>
								</tr>';
		}
		$out .=	'			</tbody>
						</table>
					</div>
				</div>';
	}
	else
	{
		$cmd = $_GET['cmd'];
		if($cmd == 'create')
		{
			array_push($nav, array($lang->get('ext_menu_acp_action_create'), '', ''));
			if(isset($_POST['submit']))
			{
				$error = array();
				if(empty($_POST['name'])) array_push($error, $lang->get('ext_menu_acp_create_err_noname'));
				$query = $db->prepare("SELECT NULL FROM `ext_menu` WHERE `name` = ?");
				$query->execute([$_POST['name']]);
				if($query->rowCount()) array_push($error, $lang->get('ext_menu_acp_create_err_taken'));
				if(empty($error))
				{
					$query = $db->prepare('INSERT INTO `ext_menu` (`name`) VALUES (?)');
					$query->execute([$_POST['name']]);
					$id = $db->lastInsertId();
					$out .= $theme->dat('acp', 'alert', 'success', $lang->get('ext_menu_acp_create_success', $_POST['name'], $id));
					header('refresh:3;url=?e_menu_main&cmd='.$id);
				}
				else
				{
					$outErr = '<b>'.$lang->get('acp_unable').'</b>';
					$errCount = 0;
					foreach($error as $err)
						$outErr .= '<br><b>'.++$errCount.'</b>. '.$err;
					$out .= $theme->dat('acp', 'alert', 'danger', $outErr);
				}
				
			}
			$out .= '<form method="POST" role="form">
						<div class="col-lg-4">
							<div class="form-group">';
			$out .= $theme->dat('acp', 'form_field', $lang->get('ext_menu_acp_name'), 'name', 'maxlength="16"');
			$out .= $theme->dat('acp', 'form_field_help', $lang->get('ext_menu_acp_create_help'));
			$out .= '		</div>
							<center>
								<input class="btn btn btn-success" name="submit" type="submit" value="'.$lang->get('ext_menu_acp_create').'">
							</center>
						</div>
						<div class="col-lg-8">
							<div class="panel panel-info">
								<div class="panel-heading">
									<h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
								</div>
								<div class="panel-body">'.$lang->get('ext_menu_acp_create_info').'</div>
							</div>
						</div>
					</form>';
		}
		elseif(is_numeric($cmd))
		{
			$url = '?e_menu_main&cmd='.$cmd;
			array_push($nav, array($lang->get('ext_menu_acp_action_modify', $cmd), '', ''));
			$query = $db->prepare('SELECT `name` FROM `ext_menu` WHERE `id` = ?');
			$query->execute([$cmd]);
			$menu = $query->fetchColumn();
			if(isset($_POST['rename']))
			{
				$query = $db->prepare('UPDATE `ext_menu` SET `name` = ? WHERE `id` = ?');
				$query->execute([$_POST['name'], $cmd]);
				header('location: '.$url);
			}
			if(isset($_POST['mod']) && !empty($_POST['mod_item']) && !empty($_POST['mod_link']) && !empty($_POST['mod_sort']) && is_numeric($_POST['mod_sort']))
			{
				$query = $db->prepare('UPDATE `ext_menu_items` SET `item` = ?, `link` = ?, `sort` = ? WHERE `id` = ?');
				$query->execute([$_POST['mod_item'], $_POST['mod_link'], $_POST['mod_sort'], $_POST['mod_id']]);
				header('location: '.$url);
			}
			if(isset($_GET['r']))
			{
				$query = $db->prepare("DELETE FROM `ext_menu_items` WHERE `id` = ?");
				$query->execute([$_GET['r']]);
				header('location: '.$url);
			}
			if(isset($_POST['add']) && !empty($_POST['add_item']) && !empty($_POST['add_link']) && !empty($_POST['add_sort']) && is_numeric($_POST['add_sort']))
			{
				$query = $db->prepare("INSERT INTO `ext_menu_items` (`menu`, `item`, `link`, `sort`) VALUES (?, ?, ?, ?)");
				$query->execute([$cmd, $_POST['add_item'], $_POST['add_link'], $_POST['add_sort']]);
				header('location: '.$url);
			}
			$query = $db->prepare('SELECT * FROM `ext_menu_items` WHERE `menu` = ? ORDER BY `sort` ASC');
			$query->execute([$cmd]);
			$res = $query->fetchAll();
			$out .= '	<div class="col-lg-4">
							<form method="POST" role="form">
								<div class="form-group">';
					$out .= $theme->dat('acp', 'form_field', $lang->get('ext_menu_acp_name'), 'name', 'value="'.$menu.'" maxlength="16"');
					$out .= $theme->dat('acp', 'form_field_help', $lang->get('ext_menu_acp_modify_help'));
					$out .= '	</div>
								<center>
									<input class="btn btn btn-success" name="rename" type="submit" value="'.$lang->get('ext_menu_acp_modify').'">
								</center>
							</form>
						</div>
						<div class="col-lg-8">
							<div class="panel panel-info">
								<div class="panel-heading">
									<h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
								</div>
								<div class="panel-body">'.$lang->get('ext_menu_acp_modify_info').'</div>
							</div>
						</div>
					<div class="col-lg-12">
						<div class="table-responsive">
							<table class="table table-bordered table-hover table-striped">
								<thread>
									<tr>
										<th>'.$lang->get('ext_menu_acp_modify_id').'</th>
										<th>'.$lang->get('ext_menu_acp_modify_menu').'</th>
										<th>'.$lang->get('ext_menu_acp_modify_item').'</th>
										<th>'.$lang->get('ext_menu_acp_modify_link').'</th>
										<th>'.$lang->get('ext_menu_acp_modify_sort').'</th>
										<th>'.$lang->get('ext_menu_acp_modify_action').'</th>
									</tr>
								</thread>
								<tbody>';
			foreach($res as $row)
			{
				$out .=	'			<form method="POST" role="form">
										<input type="hidden" name="mod_id" value="'.$row['id'].'">
										<tr>
											<td class="col-md-1">#'.$row['id'].'</td>
											<td class="col-md-1">'.$menu.' (#'.$row['menu'].')</td>
											<td class="col-md-1">
												<input class="form-control" name="mod_item" id="mod_item" placeholder="'.$lang->get('ext_menu_acp_modify_item').'" value="'.$row['item'].'" maxlength="32">
											</td>
											<td class="col-md-1">
												<input class="form-control" name="mod_link" id="mod_link" placeholder="'.$lang->get('ext_menu_acp_modify_link').'" value="'.$row['link'].'" maxlength="32">
											</td>
											<td class="col-md-1">
												<input class="form-control" name="mod_sort" id="mod_sort" placeholder="'.$lang->get('ext_menu_acp_modify_sort').'" type="number" value="'.$row['sort'].'" maxlength="11">
											</td>
											<td class="col-md-1">
												<input class="btn btn-sm btn-success" name="mod" type="submit" value="'.$lang->get('ext_menu_acp_modify_action_modify').'">
												<a href="'.$url.'&r='.$row['id'].'" class="btn btn-sm btn-danger" role="button">'.$lang->get('ext_menu_acp_modify_action_remove').'</a>
											</td>
										</tr>
									</form>';
			}
			$out .=	'
									<form method="POST" role="form">
										<tr>
											<td class="col-md-1">-</td>
											<td class="col-md-1">'.$menu.' (#'.$cmd.')</td>
											<td class="col-md-1">
												<input class="form-control" name="add_item" id="add_item" placeholder="'.$lang->get('ext_menu_acp_modify_item').'" maxlength="32">
											</td>
											<td class="col-md-1">
												<input class="form-control" name="add_link" id="add_link" placeholder="'.$lang->get('ext_menu_acp_modify_link').'" maxlength="32">
											</td>
											<td class="col-md-1">
												<input class="form-control" name="add_sort" id="add_sort" placeholder="'.$lang->get('ext_menu_acp_modify_sort').'" type="number" maxlength="11">
											</td>
											<td class="col-md-1">
												<input class="btn btn-sm btn-success" name="add" type="submit" value="'.$lang->get('ext_menu_acp_modify_action_add').'">
											</td>
										</tr>
									</form>
								</tbody>
							</table>
						</div>
					</div>';
		}
		elseif(preg_match('/d_([0-9]+)/', $cmd, $buff) === 1)
		{
			array_push($nav, array($lang->get('ext_menu_acp_delete', $cmd = $buff[1]), '', ''));

			$query = $db->prepare("SELECT * FROM `ext_menu` WHERE `id` = ?");
			$query->execute([$cmd]);
			$res = $query->fetch(PDO::FETCH_ASSOC);

			if(isset($_POST['delete']))
			{
				// Delete menu
				$query = $db->prepare('DELETE FROM `ext_menu` WHERE `id` = ?');
				$query->execute([$cmd]);
				
				// Delete menu items
				$query = $db->prepare('DELETE FROM `ext_menu_items` WHERE `menu` = ?');
				$query->execute([$cmd]);

				$out .= $theme->dat('acp', 'alert', 'success', $lang->get('ext_menu_acp_delete_success', $res['name'], $res['id']));
				header('refresh:3;url=?e_menu_main');
			}
			else
			{
				$outFormat = '
				<form method="POST" role="form">
					<center>
						'.$lang->get('ext_menu_acp_delete_warning', $res['name'], $res['id']).'<br><br>
						<input class="btn btn-sm btn-danger" name="delete" type="submit" value="'.$lang->get('ext_menu_acp_delete_submit').'">
						<a href="?e_menu_main" class="btn btn-sm btn-info" role="button">'.$lang->get('ext_menu_acp_delete_cancel').'</a>
					</center>
				</form>';
				$out .= $theme->dat('acp', 'alert', 'danger', $outFormat);
			}
		}
		else
		{
			array_push($nav, array($lang->get('acp_error'), '', ''));
			$out .= $theme->dat('acp', 'alert', 'danger', $lang->get('acp_unavailcmd'));
		}
	}

	// Push module
	$module = array(
		'nav'			=> $nav,
		'name'			=> $lang->get('ext_menu_acp'),
		'permission'	=> 'ext_mnu',
		'output'		=> $out
	);
?>
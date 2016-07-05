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

	// ACP module: messages
	if(!IN_SYSTEM) exit;

	$out = '';
	$nav = array(array($lang->get('acp_m_messages'), $theme->dat('nav', 'acp_m_messages'), '?messages'));

	if(!isset($_GET['id']))
	{
		$query = $db->prepare('SELECT * FROM `messages` WHERE `to` = ? ORDER BY `time` DESC');
		$query->execute(array($auth['id']));
		$res = $query->fetchAll();
		$out .= '<div class="col-lg-12">
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-striped">
							<thread>
								<tr>
									<th>'.$lang->get('acp_m_messages_subject').'</th>
									<th>'.$lang->get('acp_m_messages_sender').'</th>
									<th>'.$lang->get('acp_m_messages_time').'</th>
									<th>'.$lang->get('acp_m_messages_tview').'</th>
								</tr>
							</thread>
							<tbody>';
		foreach($res as $row) 
		{
			$out .=	'			<tr>
									<td class="col-md-4">'.((!$row['read'] ? '<i class="fa fa-envelope"></i><i> ' : '') . $row['subject'] . (!$row['read'] ? '</i>' : '')).'</td>
									<td class="col-md-3">'.$row['sender'].'</td>
									<td class="col-md-3">'.date($settings['time_fl'], $row['time']).'</td>
									<td class="col-md-2"><a href="?messages&id='.$row['id'].'" class="btn btn-sm btn-primary" role="button">'.$lang->get('acp_m_messages_tview').'</a></td>
								</tr>';
		}
		$out .=	'			</tbody>
						</table>
					</div>
				</div>';
	}
	else
	{
		$query = $db->prepare('SELECT * FROM `messages` WHERE `id` = ?');
		$query->execute(array($_GET['id']));
		$res = $query->fetch(PDO::FETCH_ASSOC);
		if($res['to'] != $auth['id']) header("location: ?messages");
		if(isset($_POST['delete']))
		{
			$query = $db->prepare('DELETE FROM `messages` WHERE `id` = ?');
			$query->execute(array($_GET['id']));
			header("location: ?messages");
		}
		if(!$res['read'])
		{
			$query = $db->prepare('UPDATE `messages` SET `read` = 1 WHERE `id` = ?');
			$query->execute(array($_GET['id']));
		}
		array_push($nav, array($lang->get('acp_m_messages_view'), '', ''));
		$out .= '<form method="POST" role="form">
					<div class="col-lg-3">
						<div class="form-group">';
		$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_messages_subject'), 'subject', 'value="'.$res['subject'].'" disabled');
		$out .= '		</div>
						<div class="form-group">';
		$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_messages_sender'), 'sender', 'value="'.$res['sender'].'" disabled');
		$out .= '		</div>
						<div class="form-group">';
		$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_messages_time'), 'time', 'value="'.date($settings['time_fl'], $res['time']).'" disabled');
		$out .= '		</div>
					</div>
					<div class="col-lg-9">
						<div class="form-group">';
		$out .= $theme->dat('acp', 'form_textarea', $lang->get('acp_m_messages_content'), 'message', $res['content'], 'rows="10" disabled');
		$out .= '		</div>
						<div class="form-group">
							<a href="?messages" class="btn btn btn-info" role="button">'.$lang->get('acp_m_messages_back').'</a>
							<input class="btn btn btn-danger" name="delete" type="submit" value="'.$lang->get('acp_m_messages_delete').'">
						</div>
					</div>
				</form>';
	}

	$module = array(
		'nav'			=> $nav,
		'name'			=> $lang->get('acp_m_messages'),
		'permission'	=> '',
		'output'		=> $out
	);
?>
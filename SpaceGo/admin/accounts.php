<?php
	// ACP module: accounts
	if(!IN_SYSTEM) exit;

	$out = '';
	$nav = array(array($lang->get('acp_m_accounts'), $theme->dat('nav', 'acp_m_accounts'), '?accounts'));

	if(!isset($_GET['cmd']))
	{
		$query = $db->prepare('SELECT * FROM `accounts`');
		$query->execute();
		$res = $query->fetchAll();
		$out .= '<div class="col-lg-12">
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-striped">
							<thread>
								<tr>
									<th>'.$lang->get('acp_m_accounts_id').'</th>
									<th>'.$lang->get('acp_m_accounts_username').'</th>
									<th>'.$lang->get('acp_m_accounts_email').'</th>
									<th>'.$lang->get('acp_m_accounts_lastact').'</th>
									<th>
										<center>
											<a href="?accounts&cmd=create" class="btn btn-sm btn-success" role="button">'.$lang->get('acp_m_accounts_action_create').'</a>
										</center>
									</th>
								</tr>
							</thread>
							<tbody>';
		foreach($res as $row)
		{
			$out .=	'			<tr>
									<td class="col-md-1">'.$row['id'].'</td>
									<td class="col-md-1">'.$row['username'].'</td>
									<td class="col-md-2">'.$row['email'].'</td>
									<td class="col-md-2">'.(!$row['connection'] ? '-' : date($settings['time_fl'], $row['connection'])).'</td>
									<td class="col-md-1" align="center">
										<a href="?accounts&cmd='.$row['id'].'" class="btn btn-sm btn-primary" role="button">'.$lang->get('acp_m_accounts_action_modify').'</a>
										<a href="?accounts&cmd=d_'.$row['id'].'" class="btn btn-sm btn-danger" role="button">'.$lang->get('acp_m_accounts_action_delete').'</a>
									</td>
								</tr>';
		}
		$out .=	'			</tbody>
						</table>
					</div>
				</div>';
	}
	else // Command proccessor
	{
		$cmd = $_GET['cmd'];
		if($cmd == 'create' && $sec->permission('acc_cre'))
		{
			array_push($nav, array($lang->get('acp_m_accounts_create'), 'fa fa-user', ''));
			outForm($out, $cmd);
		}
		elseif(is_numeric($cmd) && $sec->permission('acc_mod'))
		{
			array_push($nav, array($lang->get('acp_m_accounts_modify'), 'fa fa-pencil', ''));
			outForm($out, $cmd);
		}
		elseif(preg_match('/d_([0-9]+)/', $cmd, $buff) === 1 && $sec->permission('acc_del'))
		{
			array_push($nav, array($lang->get('acp_m_accounts_delete', $cmd = $buff[1]), 'fa fa-trash', ''));
			$res = $sys->loadAcc($cmd, 'username, id');
			if(isset($_POST['delete']))
			{
				// Delete account entry
				$query = $db->prepare('DELETE FROM `accounts` WHERE `id` = ?');
				$query->execute([$cmd]);

				// Delete permission entry
				$query = $db->prepare('DELETE FROM `permissions` WHERE `id` = ?');
				$query->execute([$cmd]);

				// Delete messages
				$query = $db->prepare('DELETE FROM `messages` WHERE `to` = ?');
				$query->execute([$cmd]);

				$out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_accounts_delete_success', $res['username'], $res['id']));
				header('refresh:3;url=?accounts');
			}
			else
			{
				$outFormat = '
				<form method="POST" role="form">
					<center>
						'.$lang->get('acp_m_accounts_delete_warning', $res['username'], $res['id']).'<br><br>
						<input class="btn btn-sm btn-danger" name="delete" type="submit" value="'.$lang->get('acp_m_accounts_delete_submit').'">
						<a href="?accounts" class="btn btn-sm btn-info" role="button">'.$lang->get('acp_m_accounts_delete_cancel').'</a>
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

	function outForm(&$out, $cmd)
	{
		global $sys, $db, $theme, $lang, $sec;

		if($m = is_numeric($cmd)) $res = $sys->loadAcc($cmd);

		$submitRes = null;
		if(isset($_POST['submit']))
		{
			$error = array();
			if(empty($_POST['username'])) array_push($error, $lang->get('acp_m_accounts_err_nousr'));
			if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) array_push($error, $lang->get('acp_m_accounts_err_invalemail'));
			if(!$m)
			{
				if(empty($_POST['password'])) array_push($error, $lang->get('acp_m_accounts_err_nopwd'));
				elseif(!$sec->pwdStandard($_POST['password'])) array_push($error, $lang->get('acp_m_accounts_err_weakpwd'));
				$query = $db->prepare("SELECT NULL FROM `accounts` WHERE `username` = ?");
				$query->execute(array($_POST['username']));
				if($query->rowCount()) array_push($error, $lang->get('acp_m_accounts_err_usrinuse'));
				if(empty($error))
				{
					// Accounts table
					$query = $db->prepare('INSERT INTO `accounts` (`username`, `password`, `email`) VALUES (?, ?, ?)');
					$query->bindParam(1, $_POST['username'], PDO::PARAM_STR);
					$query->bindValue(2, $sec->pwdHash($_POST['password']), PDO::PARAM_STR);
					$query->bindParam(3, $_POST['email'], PDO::PARAM_STR);
					$query->execute();
					$id = $db->lastInsertId();

					// Permissions table
					$query = $db->prepare("INSERT INTO `permissions` (`id`) VALUES (?)");
					$query->execute(array($id));
					$sec->update_perm($id, isset($_POST['perms']) ? $_POST['perms'] : null);

					// Welcome message
					$sys->sendMessage($id, $lang->get('acp_m_accounts_create_msg_sub'), $lang->get('acp_m_accounts_create_msg_con'));

					$submitRes = $lang->get('acp_m_accounts_create_success', $_POST['username'], $id);
				}
			}
			else
			{
				if(!empty($_POST['password']) && !$sec->pwdStandard($_POST['password'])) array_push($error, $lang->get('acp_m_accounts_err_weakpwd'));
				if($res['username'] != $_POST['username'])
				{
					$query = $db->prepare('SELECT NULL FROM `accounts` WHERE `username` = ?');
					$query->execute([$_POST['username']]);
					if($query->rowCount()) array_push($error, $lang->get('acp_m_accounts_err_usrinuse'));
				}
				if(empty($error))
				{
					$query = $db->prepare('UPDATE `accounts` SET `username` = :username,
						`email` = :email'.(!empty($_POST['password']) ? ', `password` = :password' : '').' WHERE `id` = :id');
					$query->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
					if(!empty($_POST['password']))
						$query->bindValue(':password', $sec->pwdHash($_POST['password']), PDO::PARAM_STR);
					$query->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
					$query->bindParam(':id', $cmd, PDO::PARAM_INT);
					$query->execute();
					$sec->update_perm($cmd, isset($_POST['perms']) ? $_POST['perms'] : null);
					$res = $sys->loadAcc($cmd);
					$submitRes = $lang->get('acp_m_accounts_modify_success', $_POST['username']);
				}
			}
			if(!empty($error))
			{
				$outErr = '<b>'.$lang->get('acp_unable').'</b>';
				$errCount = 0;
				foreach($error as $err)
					$outErr .= '<br><b>'.++$errCount.'</b>. '.$err;
				$out .= $theme->dat('acp', 'alert', 'danger', $outErr);
			}
			else $out .= $theme->dat('acp', 'alert', 'success', $submitRes);
		}

		if(!$m && $submitRes)
		{
			header('refresh:3;url=?accounts');
			return;
		}

		$val = array(
			'username'	=> (!$m ? (isset($_POST['username']) ? $_POST['username'] : '') : $res['username']),
			'email' 	=> (!$m ? (isset($_POST['email']) ? $_POST['email'] : '') : $res['email'])
		);

		$out .= '<form method="POST" role="form">
					<div class="col-lg-3">
						<div class="form-group">';
		$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_accounts_username'), 'username', 'placeholder="'.$lang->get('acp_m_accounts_help_username').'" value="'.$val['username'].'" maxlength="16"');
		$out .= '		</div>
						<div class="form-group">';
		$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_accounts_password'), 'password', 'placeholder="'.(!$m ? $lang->get('acp_m_accounts_create_help_password') : $lang->get('acp_m_accounts_modify_help_password')).'" maxlength="32"');
		$out .= '		</div>
						<div class="form-group">
							<a class="btn btn-sm btn-warning" role="button" onclick="pwdGenerate()">'.$lang->get('acp_m_accounts_pwdgen').'</a>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">';
		$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_accounts_email'), 'email', 'placeholder="'.$lang->get('acp_m_accounts_help_email').'" value="'.$val['email'].'" maxlength="32"');
		$out .= '		</div>
						<div class="form-group">
							<label>Password Strength</label>
							<div class="progress">
								<div id="pwdstr" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="1" aria-valuemax="100" style="width: 0%;">
								</div>
							</div>
						</div>
						<div class="form-group">
							<input class="btn btn btn-success" name="submit" type="submit" value="'.(!$m ? $lang->get('acp_m_accounts_create_submit') : $lang->get('acp_m_accounts_modify_submit')).'">
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>'.$lang->get('acp_m_accounts_perm').'</label>
							<select multiple class="form-control" size="8" name="perms[]">';
		foreach($sec->perms as $p)
		{
			if($p[1] == '_') $out .= '<optgroup label="'.$p[0].'">';
			else $out .= '<option value="'.$p[1].'"'.($sec->permission($p[1], $cmd) ? ' selected' : '').'>'.($p[1] == '_' ? '' : '').$lang->parse($p[0]).'</option>';
		}
		//$out .= '<option value="'.$p[1].'"'.($p[1] == '_' ? ' disabled' : '').($sec->permission($p[1], $cmd) ? ' selected' : '').'>'.($p[1] == '_' ? '' : '- ').$p[0].'</option>';
		$out .= '</select>';
		$out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_accounts_perm_wrn'));
		$out .= '
							<div align="center">
								<a class="btn btn-sm btn-primary" role="button" id="perm_sa">'.$lang->get('acp_m_accounts_perm_sa').'</a>
								<a class="btn btn-sm btn-primary" role="button" id="perm_dsa">'.$lang->get('acp_m_accounts_perm_dsa').'</a>
							</div>
						</div>
					</div>
				</form>';
		$out .= "<script>
			$('#perm_sa').on('click', function (e) { $('select option').attr('selected', true); });
			$('#perm_dsa').on('click', function (e) { $('select option').attr('selected', false); });
			$('#password').on('change', function (e) { pwdStandard(document.getElementById('password').value); });

			function pwdGenerate()
			{
				var password = '';
				var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				var current = '';
				for(i = 1; i <= 10; i++)
				{
					current = Math.floor(Math.random() * chars.length + 1);
					password += chars.charAt(current);
				}
				pwdStandard(document.getElementById('password').value = password);
			}

			function pwdStandard(passwd)
			{
				var base = 0;
				var combos = 0;
				if(passwd.match(/[a-z]/)) base = (base+26);
				if(passwd.match(/[A-Z]/)) base = (base+26);
				if(passwd.match(/\d+/)) base = (base+10);
				if(passwd.match(/[>!'#$%&'()*+,-./:;<=>?@[\]^_`{|}~]/)) base = (base+33);
				combos = Math.pow(base, passwd.length);
				if(combos == 1) str = 0;
				else if(combos > 1 && combos < 1000000) str = 20;
				else if(combos >= 1000000 && combos < 1000000000000) str = 40;
				else if(combos >= 1000000000000 && combos < 1000000000000000000) str = 60;
				else if(combos >= 1000000000000000000 && combos < 1000000000000000000000000) str = 80;
				else str = 100;
				$('#pwdstr').css('width', str + '%').attr('aria-valuenow', str);
			}
		</script>";
	}

	$module = array(
		'nav'			=> $nav,
		'name'			=> $lang->get('acp_m_accounts'),
		'permission'	=> 'acc',
		'output'		=> $out
	);
?>

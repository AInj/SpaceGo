<?php
	// ACP module: settings
	if(!IN_SYSTEM) exit;

	$out = '';

	if(isset($_POST['submit']))
	{
		$error = array();
		if(!$sec->pwdVerify($_POST['password'], $auth['password']))
			array_push($error, $lang->get('acp_m_settings_msg_invalidpwd'));
		if($_POST['email'] != $auth['email'] && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
			array_push($error, $lang->get('acp_m_settings_msg_invalidemail'));
		if(!empty($_POST['change_password']))
		{
			if($_POST['change_password'] != $_POST['confirm_password'])
				array_push($error, $lang->get('acp_m_settings_msg_nopwdmatch'));		
			if(!$sec->pwdStandard($_POST['change_password']))
				array_push($error, $lang->get('acp_m_settings_msg_weakpwd'));
		}
		if(empty($error))
		{
			$query = $db->prepare('UPDATE `accounts` SET `email` = :email'.(!empty($_POST['change_password']) ? ', `password` = :password' : '').' WHERE `id` = :id');
			$query->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
			if(!empty($_POST['change_password']))
				$query->bindValue(':password', $sec->pwdHash($_POST['change_password']), PDO::PARAM_STR);
			$query->bindParam(':id', $auth['id'], PDO::PARAM_STR);
			$query->execute();
			$auth = $sys->loadAcc($auth['id']);
			$out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_settings_msg_success'));
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
				<div class="col-lg-3">
					<div class="form-group">';
	$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_settings_username'), 'username', 'value="'.$auth['username'].'" disabled');
	$out .= '		</div>
					<div class="form-group">';
	$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_settings_email'), 'email', 'value="'.$auth['email'].'" maxlength="32"');
	$out .= '		</div>
					<div class="form-group has-warning">';
	$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_settings_password'), 'password', 'type="password" maxlength="32"');
	$out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_settings_password_help'));
	$out .= '		</div>
				</div>
				<div class="col-lg-3">
					<div class="form-group">';
	$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_settings_password_change'), 'change_password', 'type="password" maxlength="32"');
	$out .= '		</div>
					<div class="form-group">';
	$out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_settings_password_confirm'), 'confirm_password', 'type="password" maxlength="32"');
	$out .= '		</div>
					<input class="btn btn btn-success" name="submit" type="submit" value="'.$lang->get('acp_m_settings_submit').'">
				</div>
				<div class="col-lg-6">
					<div class="panel panel-info">
						<div class="panel-heading">
							<h3 class="panel-title">'.$lang->get('acp_m_settings_info').'</hr>
						</div>
						<div class="panel-body">'.$lang->get('acp_m_settings_info_txt').'</div>
					</div>
				</div>
			</form>';

	$module = array(
		'nav'			=> array($lang->get('acp_m_settings'), $theme->dat('nav', 'acp_m_settings'), '?settings'),
		'name'			=> $lang->get('acp_m_settings'),
		'permission'	=> '',
		'output'		=> $out
	);
?>
<?php
	// ACP module: system
	if(!IN_SYSTEM) exit;

	$out = '';
	$nav = array(array($lang->get('acp_m_system'), $theme->dat('nav', 'acp_m_system'), '?system'));

	$sections = array(
		['Settings',	'fa fa-gears',			'settings',		'sys_set'],
		['Theme',		'fa fa-paint-brush',	'theme',		'sys_the'],
		['Language',	'fa fa-language', 		'language',		'sys_lng'],
		['Security',	'fa fa-lock', 			'security',		'sys_sec'],
		['Logs',		'fa fa-eye', 			'logs',			'sys_log'],
		['Backups',		'fa fa-archive',		'backups',		'sys_bks'],
		['Updates',		'fa fa-cloud',			'updates',		'sys_set'],
		['Extensions',	'fa fa-plug',			'extensions',	'sys_ext']
	);

	if(!isset($_GET['cmd']))
	{
		$out .= '<div class="col-lg-6">
					<div class="panel panel-info">
						<div class="panel-heading">
							<h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
						</div>
						<div class="panel-body">
							'.$lang->get('acp_m_system_info_txt').'
						</div>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="panel panel-danger">
						<div class="panel-heading">
							<h3 class="panel-title">'.$lang->get('acp_m_system_sysinfo').'</hr>
						</div>
						<div class="panel-body">
							<div class="col-lg-6">
								<b>'.$lang->get('acp_m_system_sysinfo_env').':</b> '.php_uname('s').' ('.php_uname('r').')<br>
								<b>'.$lang->get('acp_m_system_sysinfo_ver').':</b> '.$build['version'].'<br>
								<b>'.$lang->get('acp_m_system_sysinfo_ins').':</b> '.date('d/m/Y, H:i', $settings['sys_installed']).'
							</div>
							<div class="col-lg-6">
								<b>'.$lang->get('acp_m_system_sysinfo_theme').':</b> '.$settings['sys_theme'].'<br>
								<b>'.$lang->get('acp_m_system_sysinfo_lang').':</b> '.$settings['sys_language'].'<br>
								<b>'.$lang->get('acp_m_system_sysinfo_lastbk').':</b> '.date('d/m/Y, H:i', $settings['sys_lastbk']).'
							</div>
						</div>
					</div>
				</div>';
		$aSections = 0;
		foreach($sections as $s) if($sec->permission($s[3]))
		{
			$aSections++;
			$out .= '<div class="col-lg-3 text-center">
						<div class="panel panel-default">
							<a href="?system&cmd='.$s[2].'" style="color: #000000;">
								<div class="panel-body">
									<i class="'.$s[1].'" style="font-size: 1.5em;"></i><br>
									<div style="font-size: 1.8em;">'.$s[0].'</div>
								</div>
							</a>
						</div>
					</div>';
		}
		if(!$aSections)
			$out .= '<div class="col-lg-12">'.$theme->dat('acp', 'alert', 'warning', $lang->get('acp_m_system_nosections')).'</div>';
	}
	elseif(in_array_r($cmd = $_GET['cmd'], $sections) && $sec->permission($sections[$sID = array_search($cmd, array_column($sections, 2))][3])) // טחינה #?
	{ // Command proccessor
		array_push($nav, array($sections[$sID][0], $sections[$sID][1], '?system&cmd='.$sections[$sID][2]));
		if(file_exists($inc = ROOT_DIR . "/admin/system/" . $cmd . ".php")) include($inc);
	}
	else
	{
		array_push($nav, array($lang->get('acp_error'), '', ''));
		$out .= $theme->dat('acp', 'alert', 'danger', $lang->get('acp_unavailcmd'));
	}

	$module = array(
		'nav'			=> $nav,
		'name'			=> $lang->get('acp_m_system'),
		'permission'	=> '',
		'output'		=> $out
	);
?>

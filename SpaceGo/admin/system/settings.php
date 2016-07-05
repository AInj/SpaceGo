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

    // ACP module: system/settings
    if(!IN_SYSTEM) exit;

    if(isset($_POST['submit']))
    {
        $required = [ 'webname', 'timezone', 'timefs', 'timefl' ];
        foreach($required as $req) if(empty($_POST[$req]))
        {
            $err = $out .= $theme->dat('acp', 'alert', 'danger', $lang->get('acp_m_system_settings_fail'));
            break;
        }
        if(!isset($err))
        {
            $query = $db->prepare('UPDATE `settings` SET `web_name` = ?, `meta_desc` = ?, `meta_keys` = ?, `meta_author` = ?, `time_timezone` = ?, `time_fs` = ?, `time_fl` = ?');
            $query->bindParam(1, $_POST['webname'], PDO::PARAM_STR);
            $query->bindParam(2, $_POST['metadesc'], PDO::PARAM_STR);
            $query->bindParam(3, $_POST['metakeys'], PDO::PARAM_STR);
            $query->bindParam(4, $_POST['metaauthor'], PDO::PARAM_STR);
            $query->bindParam(5, $_POST['timezone'], PDO::PARAM_STR);
            $query->bindParam(6, $_POST['timefs'], PDO::PARAM_INT);
            $query->bindParam(7, $_POST['timefl'], PDO::PARAM_INT);
            $query->execute();
            $sys->loadSet($settings);
            $out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_system_settings_success'));
        }
    }
    $req = ' <font color="red"><b>*</b></font>';
    $out .= '<form method="POST" role="form">
                <div class="col-lg-3">
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_settings_webname').$req, 'webname', 'value="'.$settings['web_name'].'" maxlength="32"');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_settings_metadesc'), 'metadesc', 'value="'.$settings['meta_desc'].'" maxlength="64"');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_settings_metakeys'), 'metakeys', 'value="'.$settings['meta_keys'].'" maxlength="32"');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_settings_metaauthor'), 'metaauthor', 'value="'.$settings['meta_author'].'" maxlength="16"');
    $out .= '		</div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_settings_timezone').$req, 'timezone', 'value="'.$settings['time_timezone'].'" maxlength="32"');
    $out .= $theme->dat('acp', 'form_field_help', '
        <a href="http://php.net/manual/en/timezones.php" target="_blank">'.$lang->get('acp_m_system_settings_timezone_h').'</a>');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_settings_timefs').$req, 'timefs', 'value="'.$settings['time_fs'].'" maxlength="32"');
    $out .= $theme->dat('acp', 'form_field_help', '
        <a href="http://php.net/manual/en/function.date.php" target="_blank">'.$lang->get('acp_m_system_settings_timef_h').'</a>');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_settings_timefl').$req, 'timefl', 'value="'.$settings['time_fl'].'" maxlength="64"');
    $out .= $theme->dat('acp', 'form_field_help', '
        <a href="http://php.net/manual/en/function.date.php" target="_blank">'.$lang->get('acp_m_system_settings_timef_h').'</a>');
    $out .= '		</div>
                </div>
                <div class="col-lg-6">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                        </div>
                        <div class="panel-body">'.$lang->get('acp_m_system_settings_info').'</div>
                    </div>
                    <center>
                        <input class="btn btn btn-success" name="submit" type="submit" value="'.$lang->get('acp_m_system_settings_save').'">
                    </center>
                </div>
            </form>';
?>

<?php
    // ACP module: system/security
    if(!IN_SYSTEM) exit;

    $list = isset($_GET['list']) ? $_GET['list'] : '';
    if($list == 'ban')
    {
        $url = '?system&cmd='.$sections[$sID][2].'&list=ban';
        array_push($nav, array('Ban List', 'fa fa-ban', $url));
        if(isset($_GET['r']) && is_numeric($_GET['r']))
        {
            $query = $db->prepare("DELETE FROM `bans` WHERE `id` = ?");
            $query->execute([$_GET['r']]);
            header('location: '.$url);
        }
        $query = $db->prepare('SELECT * FROM `bans`');
        $query->execute();
        $res = $query->fetchAll();
        $out .= '<form method="POST" role="form">
                    <div class="col-lg-12">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                            </div>
                            <div class="panel-body">'.$lang->get('acp_m_system_security_ban_info').'</div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thread>
                                    <tr>
                                        <th>'.$lang->get('acp_m_system_security_ban_type').'</th>
                                        <th>'.$lang->get('acp_m_system_security_ban_ip').'</th>
                                        <th>'.$lang->get('acp_m_system_security_ban_time').'</th>
                                        <th>'.$lang->get('acp_m_system_security_ban_expiry').'</th>
                                        <th>'.$lang->get('acp_m_system_security_ban_reason').'</th>
                                        <th>'.$lang->get('acp_m_system_security_ban_action').'</th>
                                    </tr>
                                </thread>
                                <tbody>';
            foreach($res as $row)
            {
                $out .=	'			<tr>
                                        <td class="col-md-1">'.$row['type'].'</td>
                                        <td class="col-md-1">'.$row['ip'].'</td>
                                        <td class="col-md-2">'.date($settings['time_fl'], $row['time']).'</td>
                                        <td class="col-md-2">'.date($settings['time_fl'], $row['expiry']).'</td>
                                        <td class="col-md-2">'.$row['reason'].'</td>
                                        <td class="col-md-1">
                                            <a href="'.$url.'&r='.$row['id'].'" class="btn btn-sm btn-danger" role="button">'.$lang->get('acp_m_system_security_ban_action_remove').'</a>
                                        </td>
                                    </tr>';
            }
            $out .=	'
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>';
    }
    elseif($list == 'admacl')
    {
        $url = '?system&cmd='.$sections[$sID][2].'&list=admacl';
        array_push($nav, array('Admin ACL', 'fa fa-list', $url));
        if(isset($_GET['r']))
        {
            $query = $db->prepare('SELECT * FROM `admacl` WHERE `host` = ?');
            $query->execute([$_GET['r']]);
            if($query->rowCount())
            {
                $query = $db->prepare("DELETE FROM `admacl` WHERE `host` = ?");
                $query->execute([$_GET['r']]);
            }
            header('location: '.$url);
        }
        if(isset($_POST['add']) && !empty($_POST['host']))
        {
            $query = $db->prepare('SELECT * FROM `admacl` WHERE `host` = ?');
            $query->execute([$_POST['host']]);
            if(!$query->rowCount())
            {
                $query = $db->prepare("INSERT INTO `admacl` (`host`, `comment`) VALUES (?, ?)");
                $query->execute([$_POST['host'], $_POST['comment']]);
                $out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_system_security_admacl_added', $_POST['host'], $_POST['comment']));
            }
        }
        if(isset($_POST['toggle']))
        {
            $query = $db->prepare('UPDATE `settings` SET `sec_admacl` = ?');
            $query->execute([!$settings['sec_admacl']]);
            $sys->loadSet($settings);
            if($settings['sec_admacl'])
            {
                $query = $db->prepare('SELECT * FROM `admacl` WHERE `host` = ?');
                $query->execute([$_SERVER['REMOTE_ADDR']]);
                if(!$query->rowCount())
                {
                    $query = $db->prepare("INSERT INTO `admacl` (`host`, `comment`) VALUES (?, 'AUTO')");
                    $query->execute([$_SERVER['REMOTE_ADDR']]);
                }
            }
            $out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_system_security_admacl_toggled', $settings['sec_admacl'] ? $lang->get('acp_m_system_security_admacl_on') : $lang->get('acp_m_system_security_admacl_off')));
        }
        $query = $db->prepare('SELECT * FROM `admacl`');
        $query->execute();
        $res = $query->fetchAll();
        $out .= '<form method="POST" role="form">
                    <div class="col-lg-6">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thread>
                                    <tr>
                                        <th>'.$lang->get('acp_m_system_security_admacl_host').'</th>
                                        <th>'.$lang->get('acp_m_system_security_admacl_comment').'</th>
                                        <th>'.$lang->get('acp_m_system_security_admacl_action').'</th>
                                    </tr>
                                </thread>
                                <tbody>';
            foreach($res as $row)
            {
                $out .=	'			<tr>
                                        <td class="col-md-1">'.$row['host'].'</td>
                                        <td class="col-md-1">'.$row['comment'].'</td>
                                        <td class="col-md-1">
                                            <a href="'.$url.'&r='.$row['host'].'" class="btn btn-sm btn-danger" role="button">'.$lang->get('acp_m_system_security_admacl_action_remove').'</a>
                                        </td>
                                    </tr>';
            }
            $out .=	'
                                    <tr>
                                        <td class="col-md-1">
                                            <input class="form-control" name="host" id="host" placeholder="'.$lang->get('acp_m_system_security_admacl_host').'" maxlength="32">
                                        </td>
                                        <td class="col-md-1">
                                            <input class="form-control" name="comment" id="comment" placeholder="'.$lang->get('acp_m_system_security_admacl_comment').'" maxlength="10">
                                        </td>
                                        <td class="col-md-1">
                                            <input class="btn btn-sm btn-success" name="add" type="submit" value="'.$lang->get('acp_m_system_security_admacl_action_add').'">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                            </div>
                            <div class="panel-body">'.$lang->get('acp_m_system_security_admacl_info').'</div>
                        </div>
                        <center>
                            <input class="btn btn btn-primary" name="toggle" type="submit" value="'.$lang->get('acp_m_system_security_admacl_toggle', $settings['sec_admacl'] ? $lang->get('acp_m_system_security_admacl_on') : $lang->get('acp_m_system_security_admacl_off')).'">
                        </center>
                    </div>
                </form>';
    }
    else
    {
        if(isset($_POST['submit']))
        {
            $required = [ 'pwdstandard', 'authatts', 'authbanl' ];
            foreach($required as $req) if(empty($_POST[$req]))
            {
                $err = $out .= $theme->dat('acp', 'alert', 'danger', $lang->get('acp_m_system_settings_fail'));
                break;
            }
            if(!isset($err))
            {
                $query = $db->prepare('UPDATE `settings` SET `sec_pwdstandard` = ?, `sec_authatts` = ?, `sec_authbanl` = ?');
                $query->bindParam(1, $_POST['pwdstandard'], PDO::PARAM_INT);
                $query->bindParam(2, $_POST['authatts'], PDO::PARAM_INT);
                $query->bindParam(3, $_POST['authbanl'], PDO::PARAM_INT);
                $query->execute();
                $sys->loadSet($settings);
                $out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_system_security_success'));
            }
        }
        $req = ' <font color="red"><b>*</b></font>';
        $out .= '<form method="POST" role="form">
                    <div class="col-lg-3">
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_security_pwdhashalgo'), 'pwdhashalgo', 'value="'.$build['sec_pwdhashalgo'].'" disabled');
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_security_pwdhashcost'), 'pwdhashcost', 'value="'.$build['sec_pwdhashcost'].'" disabled');
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_security_pwdstandard').$req, 'pwdstandard', 'value="'.$settings['sec_pwdstandard'].'" maxlength="11"');
        $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_security_pwdstandard_h'));
        $out .= '		</div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_security_authatts').$req, 'authatts', 'value="'.$settings['sec_authatts'].'" maxlength="2"');
        $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_security_authatts_h'));
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_security_authbanl').$req, 'authbanl', 'value="'.$settings['sec_authbanl'].'" maxlength="11"');
        $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_security_authbanl_h'));
        $out .= '		</div>
                        <center>
                            <input class="btn btn-sm btn-success" name="submit" type="submit" value="'.$lang->get('acp_m_system_settings_save').'">
                        </center>
                    </div>
                    <div class="col-lg-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                            </div>
                            <div class="panel-body">'.$lang->get('acp_m_system_security_info').'</div>
                        </div>
                    </div>
                </form>';
    }
?>

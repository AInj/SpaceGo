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

    // ACP module: system/logs
    if(!IN_SYSTEM) exit;

    /*
    TODO: Deploy loggers
    $out .= 'Hello ' . $sys->getUsername($auth['id']) . ', I see you';
    $dbg->dbLog('test', 'data and ' . $sys->getUsername($auth['id']), $auth['id']);
    $dbg->dbLog('accounts', $sys->getUsername($auth['id']) . ' edited account Ran', $auth['id']);*/

    $view = isset($_GET['view']) ? $_GET['view'] : null;
    if(isset($_POST['view']))
    {
        $session->set('admin_header_post', $_POST);
        header('location: ?system&cmd=logs&view=' . $_POST['view']);
    }

    if(!isset($view))
    {
        $out .= '<div class="col-lg-6">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_logs_lvl1').'</hr>
                        </div>
                        <div class="panel-body">'.$lang->get('acp_m_system_logs_lvl1_info').'</div>
                    </div>
                    <form method="POST" role="form">
                        <div class="form-group">
                            <select class="form-control" size="5" name="view">'.scanner().'</select>
                        </div>
                        <center>
                            <input class="btn btn btn-primary" type="submit" value="'.$lang->get('acp_m_system_logs_view').'"><br>';
        $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_logs_path', $dbg->log));
        $out .= '
                        </center>
                    </form>
                </div>
                <div class="col-lg-6">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_logs_lvl2').'</hr>
                        </div>
                        <div class="panel-body">'.$lang->get('acp_m_system_logs_lvl2_info').'</div>
                    </div>
                    <form method="POST" class="form-inline" role="form">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <div class="form-group">
                                <select class="form-control" name="view">
                                    <option value="" disabled selected></option>
                                    '.dbLogTypeFetch().'
                                </select>
                                <input class="form-control btn btn-primary pull-right" type="submit" value="'.$lang->get('acp_m_system_logs_search').'">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" placeholder="'.$lang->get('acp_m_system_logs_search_ph').'" size="50" maxlength="50">
                            </div>
                        </div>
                    </form>
                </div>';
    }
    else
    {
        if(endsWith($view, '.log'))
        {
            if(!file_exists($dir = ROOT_DIR . '/' . $dbg->log . '/' . $view)) header('location: ?system&cmd=logs');
            array_push($nav, array($lang->get('acp_m_system_logs_viewing', $view), 'fa fa-file', ''));
            $out .= '
					<div class="col-lg-12">
						<div class="form-group">';
		$out .= $theme->dat('acp', 'form_textarea', '/' . $dbg->log . '/' . $view, 'log', file_get_contents($dir), 'rows="20" disabled');
		$out .= '		</div>
						<div class="form-group">
                            <center>
							    <a href="?system&cmd=logs" class="btn btn btn-info" role="button">'.$lang->get('acp_m_system_logs_back').'</a>
                            </center>
						</div>
					</div>';
        }
        else
        {
            if(!in_array($view, $dbg->dbLogTypes)) header('location: ?system&cmd=logs');
            array_push($nav, array($lang->get('acp_m_system_logs_viewing', ucfirst($view)), 'fa fa-database', ''));
            if($session->get('admin_header_post'))
            {
                $_POST = $session->get('admin_header_post');
                $session->rst('admin_header_post');
            }
            $val = array(
                'search' => isset($_POST['search']) ? $_POST['search'] : '',
                'target' => isset($_POST['target']) ? $_POST['target'] : '',
    			'sort'	=> isset($_POST['sort']) ? $_POST['sort'] : 'no',
                'limit' => isset($_POST['limit']) ? $_POST['limit'] : ''
    		);
            if(isset($_POST['refresh']))
            {
                if($view != $_POST['log'])
                {
                    $session->set('admin_header_post', $_POST);
                    header('location: ?system&cmd=logs&view=' . $_POST['log']);
                }
                if(!empty($val['limit']) && !is_numeric($val['limit'])) $val['limit'] = null;
            }
            $query = $db->prepare('SELECT * FROM `logs`
                        WHERE `log` = :log
                        AND `entry` LIKE :search
                        AND `by` LIKE :target
                    ORDER BY `time` ' . ($val['sort'] == 'no' ? 'DESC' : 'ASC') . '
                    LIMIT ' . (is_numeric($val['limit']) ? $val['limit'] : 30));
            $query->bindParam(':log', $view, PDO::PARAM_STR);
            $query->bindValue(':search', '%'.$val['search'].'%', PDO::PARAM_STR);
            $query->bindValue(':target', '%'.$val['target'].'%', PDO::PARAM_STR);
    		$query->execute();
    		$res = $query->fetchAll();
            $out .= '
            <div class="col-lg-12">
                <form method="POST" role="form">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_logs_searchfilter').'</hr>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <div class="col-xs-2">
                                    <label control-label">'.$lang->get('acp_m_system_logs_sf_log').'</label>
                                    <select class="form-control" name="log">'.dbLogTypeFetch($view).'</select>
                                </div>
                                <div class="col-xs-6">
                                    <label control-label">'.$lang->get('acp_m_system_logs_search').'</label>
                                    <input type="text" class="form-control" name="search" placeholder="'.$lang->get('acp_m_system_logs_search_ph').'" value="'.$val['search'].'" maxlength="50">
                                </div>
                                <div class="col-xs-4">
                                    <label control-label">'.$lang->get('acp_m_system_logs_sf_target').'</label>
                                    <input type="text" class="form-control" name="target" placeholder="'.$lang->get('acp_m_system_logs_sf_target_ph').'" value="'.$val['target'].'" maxlength="16">
                                </div>
                                <div class="col-xs-12" style="height:10px;"></div>
                                <div class="col-xs-3">
                                    <label control-label">'.$lang->get('acp_m_system_logs_sf_sort').'</label>
                                    <select class="form-control" name="sort">
                                        <option value="no"'.($val['sort'] == 'no' ? ' selected' : '').'>'.$lang->get('acp_m_system_logs_sf_sort_no').'</option>
                                        <option value="on"'.($val['sort'] == 'on' ? ' selected' : '').'>'.$lang->get('acp_m_system_logs_sf_sort_on').'</option>
                                    </select>
                                </div>
                                <div class="col-xs-4">
                                    <label control-label">'.$lang->get('acp_m_system_logs_sf_limit').'</label>
                                    <input type="text" class="form-control" name="limit" placeholder="'.$lang->get('acp_m_system_logs_sf_limit_ph').'" value="'.$val['limit'].'" maxlength="11">
                                </div>
                                <div class="col-xs-2">
                                    <label control-label">&nbsp;</label>
                                    <input class="form-control btn btn-primary pull-right" name="refresh" type="submit" value="'.$lang->get('acp_m_system_logs_sf_refresh').'">
                                </div>
                                <div class="col-xs-3">
                                    <label control-label">&nbsp;</label>
                                    <a href="?system&cmd=logs" class="form-control btn btn btn-info" role="button">'.$lang->get('acp_m_system_logs_back').'</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>'.$lang->get('acp_m_system_logs_sr_id').'</th>
                                <th>'.$lang->get('acp_m_system_logs_sr_time').'</th>
                                <th>'.$lang->get('acp_m_system_logs_sr_entry').'</th>
                                <th>'.$lang->get('acp_m_system_logs_sr_by').'</th>
                            </tr>
                        </thead>
                        <tbody>';
            foreach($res as $row)
            {
                $out .= '
                    <tr>
                        <td class="col-md-1">#'.sprintf("%06d", $row['id']).'</i></td>
                        <td class="col-md-3">'.date($settings['time_fl'], $row['time']).'</td>
                        <td class="col-md-6">'.$row['entry'].'</td>
                        <td class="col-md-2">'.$row['by'].'</td>
                    </tr>';
            }
            $out .= '
                        </tbody>
                    </table>
                </div>
            </div>';
        }
    }

    function scanner()
    {
        global $dbg;
        $out = '';
        foreach(glob(ROOT_DIR . '/' . $dbg->log . '/*.log') as $scan)
            $out .= '<option value="'.basename($scan).'">'.basename($scan).'</option>';
        return $out;
    }

    function dbLogTypeFetch($select = null)
    {
        global $dbg;
        $out = '';
        foreach($dbg->dbLogTypes as $type)
            $out .= '<option value="'.$type.'"'.($select ? ($select == $type ? ' selected' : '') : '').'>'.ucfirst($type).'</option>';
        return $out;
    }
?>

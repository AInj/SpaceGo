<?php
    // ACP module: system/extensions
    if(!IN_SYSTEM) exit;

    $cexts = explode(',', $settings['sys_extensions']);
    $exts = array();
    foreach(glob(ROOT_DIR . '/resource/extensions/*/manifest.xml') as $extd)
    {
        try
        {
            $xml = new SimpleXMLElement(file_get_contents($extd));
            $exts[(string)$xml->id] = get_object_vars($xml);
        }
        catch(Exception $e)
        {
            continue;
        }
    }
    if(!isset($_GET['m']))
    {
        $out .= '
        <div class="col-lg-10">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                </div>
                <div class="panel-body">
                    '.$lang->get('acp_m_system_ext_info').'
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">'.$lang->get('acp_m_system_ext_legend').'</hr>
                </div>
                <div class="panel-body">
                    '.$lang->get('acp_m_system_ext_legend_info').'
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>'.$lang->get('acp_m_system_ext').'</th>
                            <th>'.$lang->get('acp_m_system_ext_version').'</th>
                            <th>'.$lang->get('acp_m_system_ext_author').'</th>
                            <th>'.$lang->get('acp_m_system_ext_manage').'</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach($exts as $i => $e)
        {
            $status = in_array($i, $cexts) ? (isset($ext[$i]->extension) ? 'success' : 'warning') : 'danger';
            $out .= '
                <tr class="'.$status.'">
                    <td>'.$e['name'].' <i>('.$i.')</i></td>
                    <td>'.$e['version'].'</td>
                    <td>'.$e['author'].'</td>
                    <td><a href="?system&cmd=extensions&m='.$i.'" class="btn btn-sm btn-primary" role="button">'.$lang->get('acp_m_system_ext_manage').'</a></td>
                </tr>';
        }
        $out .= '
                    </tbody>
                </table>
            </div>
        </div>';
    }
    else
    {
        $url = '?system&cmd='.$sections[$sID][2].'&m=' . $e = $_GET['m'];
        if(!isset($exts[$e])) header('location: ?system&cmd=extensions');
        array_push($nav, array($exts[$e]['name'], 'fa fa-edit', $url));
        $installed = !file_exists($ins = ROOT_DIR . "/resource/extensions/{$e}/install.php");
        $status = in_array($e, $cexts);
        if(!isset($_POST['install']))
        {
            if(!$installed)
                $out .= $theme->dat('acp', 'alert', 'warning', $lang->get('acp_m_system_ext_mng_ins'));
            else if($status && !isset($ext[$e]->extension))
                $out .= $theme->dat('acp', 'alert', 'warning', $lang->get('acp_m_system_ext_mng_err'));
            if(isset($_POST['toggle']) && $installed)
            {
                $set = explode(',', $settings['sys_extensions']);
                if($status = !$status) array_push($set, $e);
                else foreach($set as $k => $v) if($v == $e) unset($set[$k]);
                $query = $db->prepare('UPDATE `settings` SET `sys_extensions` = ?');
                $query->execute([implode(',', $set)]);
            }
        }
        elseif(!$installed)
        {
            include($ins);

            $error = array();

            $missing_deps = array();
            if(!empty($depend)) foreach($depend as $dep)
                if(!isset($ext[$dep]->extension)) array_push($missing_deps, $dep);
            if(!empty($missing_deps))
                array_push($error, $lang->get('acp_m_system_ext_ins_err_deps', implode(', ', $missing_deps)));

            $invalid_files = array();
            foreach($files as $file)
                if(sha1_file(ROOT_DIR . "/resource/extensions/{$e}/" . $file[0]) != $file[1])
                    array_push($invalid_files, $file[0]);
            if(!empty($invalid_files))
                array_push($error, $lang->get('acp_m_system_ext_ins_err_files', implode(', ', $invalid_files)));

            if(empty($error) && file_exists($sqlfile = ROOT_DIR . "/resource/extensions/{$e}/{$sql}"))
                if($db->exec(file_get_contents($sqlfile)) !== 0)
                    array_push($error, $lang->get('acp_m_system_ext_ins_err_sql', $sqlfile, print_r($db->errorInfo(), true)));

            if(empty($error))
    		{
                rename($ins, $ins . '.done');
                $installed = true;
                $out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_system_ext_ins_success', $exts[$e]['name'], $e));
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
                    <div class="col-lg-2">
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_ext_mng_id'), 'id', 'value="'.$exts[$e]['id'].'" disabled');
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field',  $lang->get('acp_m_system_ext_mng_name'), 'name', 'value="'.$exts[$e]['name'].'" disabled');
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_ext_mng_ver'), 'ver', 'value="'.$exts[$e]['version'].'" disabled');
        $out .= '		</div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_ext_mng_auth'), 'auth', 'value="'.$exts[$e]['author'].'" disabled');
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_ext_mng_web'), 'web', 'value="'.$exts[$e]['website'].'" disabled');
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_ext_mng_comp'), 'comp', 'value="'.$exts[$e]['comp'].'" disabled');
        $out .= '		</div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_textarea', $lang->get('acp_m_system_ext_mng_desc'), 'desc', $exts[$e]['desc'], 'rows="8" disabled');
        $out .= '		</div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">'.$lang->get('acp_m_system_ext_mng').'</hr>
                            </div>
                            <div class="panel-body">'.$lang->get($installed ? 'acp_m_system_ext_mng_info' : 'acp_m_system_ext_ins_info').'</div>
                        </div>
                        <center>';
        if($installed)
            $out .= '<input class="btn btn btn-'.($status ? 'warning' : 'success').'" name="toggle" type="submit" value="'.$lang->get($status ? 'acp_m_system_ext_off' : 'acp_m_system_ext_on').'">';
        else
            $out .= '<input class="btn btn btn-primary" name="install" type="submit" value="'.$lang->get('acp_m_system_ext_ins').'">';
        $out .= '
                        </center>
                    </div>
                </form>';
    }
?>

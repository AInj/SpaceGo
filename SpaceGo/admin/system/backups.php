<?php
    // ACP module: system/backups
    if(!IN_SYSTEM) exit;

    $backup = new backup();

    $task = isset($_GET['task']) ? $_GET['task'] : null;
    if($task == 'exec')
    {
        $sub = isset($_POST['restore']) ? 'restore' : (isset($_POST['delete']) ? 'delete' : '');
        header('location: ?system&cmd=backups&task='.$sub.'&target=' . $_POST['target']);
    }

    if(!isset($task))
    {
        $out .= '<div class="col-lg-6">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_backups_file').'</hr>
                        </div>
                        <div class="panel-body">'.$lang->get('acp_m_system_backups_file_info').'</div>
                    </div>
                    <form action="?system&cmd=backups&task=exec" method="POST" role="form">
                        <div class="form-group">
                            <select class="form-control" size="8" name="target">'.scanner($backup, 'file').'</select>
                        </div>
                        <center>
                            <a href="?system&cmd=backups&task=create&target=file" class="btn btn btn-success" role="button">'.$lang->get('acp_m_system_backups_create').'</a>
                            <input class="btn btn btn-danger" name="delete" type="submit" value="'.$lang->get('acp_m_system_backups_delete').'">
                        </center>
                    </form>
                </div>
                <div class="col-lg-6">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_backups_db').'</hr>
                        </div>
                        <div class="panel-body">'.$lang->get('acp_m_system_backups_db_info').'</div>
                    </div>
                    <form action="?system&cmd=backups&task=exec" method="POST" role="form">
                        <div class="form-group">
                            <select class="form-control" size="8" name="target">'.scanner($backup, 'db').'</select>
                        </div>
                        <center>
                            <a href="?system&cmd=backups&task=create&target=db" class="btn btn btn-success" role="button">'.$lang->get('acp_m_system_backups_create').'</a>
                            <input class="btn btn btn-primary" name="restore" type="submit" value="'.$lang->get('acp_m_system_backups_restore').'">
                            <input class="btn btn btn-danger" name="delete" type="submit" value="'.$lang->get('acp_m_system_backups_delete').'">
                        </center>
                    </form>
                </div>';
    }
    elseif($task == 'create')
    {
        $target = isset($_GET['target']) ? $_GET['target'] : null;
        if($target != 'file' && $target != 'db') header('location: ?system&cmd=backups');
        $type = ($target == 'file');
        array_push($nav, array($lang->get('acp_m_system_backups_create'), 'fa fa-plus-square', ''));
        if(isset($_POST['submit']))
        {
            $token = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
            file_put_contents(ROOT_DIR . '/admin/system/background.token', $token);

            $session->set('admin_backup_attr', array(
                'type'    => $target,
                'flag'    => !empty($_POST['flag']) ? $_POST['flag'] : 'x',
                'notes'   => !empty($_POST['notes']) ? $_POST['notes'] : 'N/A',
                'creator' => $auth['username'],
                'skip'    => !empty($_POST['skip']) ? $_POST['skip'] : null
            ));

            $out .= '
                    <div class="col-lg-12">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">'.$lang->get('acp_m_system_backups_proc').'</hr>
                            </div>
                            <div class="panel-body">
                                '.$lang->get('acp_m_system_backups_proc_info').'
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <p id="monitor"></p>
                                <div style="text-align: center;">
                                    <p id="message"></p>
                                    <div class="progress">
                                        <div id="progress"class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                            0%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
            $out .= "
            <script>
                var stages = 0;
                var completed = 0;

                $.getJSON('system/background.php?token={$token}&task=backup&cmd=_').done(function(data)
                {
                    if(data.result === true)
                    {
                        stages = data.output;
                        stage(completed);
                    }
                });

                function stage(i)
                {
                    $.getJSON('system/background.php?token={$token}&task=backup&cmd=' + i).done(function(data)
                    {
                        if(data.result === true)
                        {
                            var add = Math.round((completed + 1) * 100 / stages);
                            $('#monitor').append(data.output + ' OK<br>');
                            $('#progress').html('%' + add);
                            $('#progress').css('width', add + '%').attr('aria-valuenow', add);
                            stage(++completed);
                            if(data.done === true)
                                $('#message').html('{$lang->get('acp_m_system_backups_proc_done')}');
                        }
                        else
                        {
                            $('#monitor').append(data.output + ' ERROR<br>');
                            $('#message').html('{$lang->get('acp_m_system_backups_proc_err')}');
                        }
                    });
                }
            </script>";
        }
        else
        {
            $out .= '<form method="POST" role="form">
                        <div class="col-lg-3">
                            <div class="form-group">';
            $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_type'), 'type', 'value="'.$lang->get($type ? 'acp_m_system_backups_file' : 'acp_m_system_backups_db').'" disabled');
            $out .= '		</div>
                            <div class="form-group">';
            $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_flag'), 'flag', 'placeholder="'.$lang->get('acp_m_system_backups_create_opt').'" maxlength="6"');
            $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_backups_create_flag_h'));
            $out .= '		</div>
                            <div class="form-group">';
            $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_notes'), 'notes', 'placeholder="'.$lang->get('acp_m_system_backups_create_opt').'" maxlength="32"');
            $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_backups_create_notes_h'));
            $out .= '		</div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">';
            $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_creator'), 'creator', 'value="'.$auth['username'].'" disabled');
            $out .= '		</div>';
            if($type)
            {
                $out .= '<div class="form-group">' .
                    $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_skip'), 'skip', 'placeholder="'.$lang->get('acp_m_system_backups_create_skip_ph').'"');
                $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_backups_create_skip_h')) . '</div>';
            }
            $out .= '
                            <center>
                                <input class="btn btn-sm btn-success" name="submit" type="submit" value="'.$lang->get('acp_m_system_backups_create').'">';
            $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_backups_create_h', $backup->dir));
            $out .= '
                            </center>
                        </div>
                        <div class="col-lg-6">
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                                </div>
                                <div class="panel-body">'.$lang->get($type ? 'acp_m_system_backups_file_info' : 'acp_m_system_backups_db_info').'</div>
                            </div>
                        </div>
                    </form>';
        }
    }
    elseif($task == 'restore')
    {
        $target = isset($_GET['target']) ? $_GET['target'] : null;
        $file = ROOT_DIR . '/' . $backup->dir . '/' . $target;
        if(is_dir($file) || !file_exists($file) || $backup->parse($file)['type'] != 'db') header('location: ?system&cmd=backups');
        array_push($nav, array($lang->get('acp_m_system_backups_restore'), 'fa fa-recycle', ''));
        $info = $backup->read($file);
        if(isset($_POST['submit']))
		{
            $out .= $backup->restore($file) ?
                $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_system_backups_restore_success', $target)) :
                $theme->dat('acp', 'alert', 'danger', '');
		}
        $out .= '<form method="POST" role="form">
                    <div class="col-lg-3">
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_type'), 'type', 'value="'.$lang->get('acp_m_system_backups_db').'" disabled');
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_flag'), 'flag', 'value="'.($info['flag'] != 'x' ? $info['flag'] : '-').'" disabled');
        $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_backups_create_flag_h'));
        $out .= '		</div>
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_notes'), 'notes', 'value="'.$info['creator_notes'].'" disabled');
        $out .= $theme->dat('acp', 'form_field_help', $lang->get('acp_m_system_backups_create_notes_h'));
        $out .= '		</div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">';
        $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_backups_create_creator'), 'creator', 'value="'.$info['creator'].'" disabled');
        $out .= '		</div>';
        $out .= '
                        <center>
                            <input class="btn btn-sm btn-warning" name="submit" type="submit" value="'.$lang->get('acp_m_system_backups_restore').'">';
        $out .= '
                        </center>
                    </div>
                    <div class="col-lg-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                            </div>
                            <div class="panel-body">'.$lang->get('acp_m_system_backups_restore_info').'</div>
                        </div>
                    </div>
                </form>';
    }
    elseif($task == 'delete')
    {
        $target = isset($_GET['target']) ? $_GET['target'] : null;
        $file = ROOT_DIR . '/' . $backup->dir . '/' . $target;
        if(is_dir($file) || !file_exists($file)) header('location: ?system&cmd=backups');
        array_push($nav, array($lang->get('acp_m_system_backups_delete'), 'fa fa-trash-o', ''));
		if(isset($_POST['submit']))
		{
            $backup->delete($target);
            header('location: ?system&cmd=backups');
		}
		else
		{
			$outFormat = '
			<form method="POST" role="form">
				<center>
					'.$lang->get('acp_m_system_backups_delete_warning', $target).'<br><br>
					<input class="btn btn-sm btn-danger" name="submit" type="submit" value="'.$lang->get('acp_m_system_backups_delete').'">
					<a href="?system&cmd=backups" class="btn btn-sm btn-info" role="button">'.$lang->get('acp_m_system_backups_delete_cancel').'</a>
				</center>
			</form>';
			$out .= $theme->dat('acp', 'alert', 'danger', $outFormat);
        }
    }

    function scanner($backup, $bk)
    {
        $out = '';
        $scanner = $backup->scan($bk);
        foreach($scanner as $file)
        {
            $parse = $backup->parse($file);
            $out .= '<option value="'.basename($file).'">
                [' . $parse['time'] . '] ' . $backup->retName($parse['type']) .
                ($parse['flag'] != 'x' ? " [{$parse['flag']}]" : '') .
            '</option>';
        }
        return $out;
    }
?>

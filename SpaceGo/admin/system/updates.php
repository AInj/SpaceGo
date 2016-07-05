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

    // ACP module: system/updates
    if(!IN_SYSTEM) exit;

    $latest = parse_ini_string(file_get_contents($build['dev_url'].'latest.ini'));
    $updated = $latest['build'] == $build['build'];
    $package = ROOT_DIR . "/cms-{$latest['build']}.zip";
    if(!isset($_GET['update']))
    {
        $out .= $theme->dat('acp', 'alert', $updated ? 'success' : 'warning', $lang->get('acp_m_system_updates_'.($updated ? '' : 'not').'updated', $latest['version'], $latest['build']));
        $out .= '
                <div class="col-lg-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_updates_note').'</hr>
                        </div>
                        <div class="panel-body">';
        if($updated) $out .= $lang->get('acp_m_system_updates_note_updated');
        else
        {
            if(!is_readable($package)) $out .= $lang->get('acp_m_system_updates_note_avail', $latest['version'], $latest['build'], $build['dev_url']);
            else
            {
                $out .= $lang->get('acp_m_system_updates_note_detected', $latest['version'], $latest['build'], $package);
                if(sha1_file($package) != $latest['checksum']) $out .= $lang->get('acp_m_system_updates_note_damaged');
                else $out .= $lang->get('acp_m_system_updates_note_ready');
            }
        }
        $out .= '
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                        </div>
                        <div class="panel-body">
                            '.$lang->get('acp_m_system_updates_info').'<br><br>
                            '.$lang->get('acp_m_system_updates_current', $build['version'], $build['build']).'
                        </div>
                    </div>
                </div>';
    }
    else
    {
        if(!is_readable($package) || sha1_file($package) != $latest['checksum']) exit(header('location: ?system&cmd=updates'));

        $token = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
        file_put_contents(ROOT_DIR . '/admin/system/background.token', $token);

        $out .= '
                <div class="col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_updates_proc').'</hr>
                        </div>
                        <div class="panel-body">
                            '.$lang->get('acp_m_system_updates_proc_info').'
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

            $.getJSON('system/background.php?token={$token}&task=update&cmd=_').done(function(data)
            {
                if(data.result === true)
                {
                    stages = data.output;
                    stage(completed);
                }
            });

            function stage(i)
            {
                $.getJSON('system/background.php?token={$token}&task=update&cmd=' + i).done(function(data)
                {
                    if(data.result === true)
                    {
                        var add = Math.round((completed + 1) * 100 / stages);
                        $('#monitor').append(data.output + ' OK<br>');
                        $('#progress').html('%' + add);
                        $('#progress').css('width', add + '%').attr('aria-valuenow', add);
                        stage(++completed);
                        if(data.done === true)
                            $('#message').html('{$lang->get('acp_m_system_updates_proc_done')}');
                    }
                    else
                    {
                        $('#monitor').append(data.output + ' ERROR<br>');
                        $('#message').html('{$lang->get('acp_m_system_updates_proc_err')}');
                    }
                });
            }
        </script>";

        /* TODO: Update package
            http://stackoverflow.com/questions/14918462/get-response-from-php-file-using-ajax
            1. run
            2. if not returned done back to 1
            3. print done, complete
            --
            Consider a UI with progress bar and automatic line updates with jQuery or so
            Check .zip file for structure and verify (checksum)
            <08:12:29> "Amit`": ‎‫א. נעילת האתר‬‎
            <08:12:41> "Amit`": ‎‫ב. קבלת רשימת הקבצים העדכניים ביותר‬‎
            <08:12:50> "Amit`": ‎‫ג. השוואה בין הקבצים שיש לרשימה שקיבלנו בב'‬‎
            <08:13:11> "Amit`": ‎‫ד. במידה ויש קבצים שונים, יצירת תיקיה "backup - conversion between vOLD & vNEW"‬‎
            <08:13:16> "Amit`": ‎‫ה. כל קובץ שהיה בו שינוי עובר לתיקיה הזו‬‎
            <08:13:54> "Amit`": ‎‫ו. מוריד את הקבצים החדשים לתיקיה הראשית ומודיע על סיום, כמו כן, מקפיץ הודעה "יתכן שתצטרך להשתמש ב SQL Converter". כשנכנסים לאתר אחרי האפדייט, ההודעה שכנראה תירשם היא "עליך לעדכן את ה-SQL באמצעות Converter... לחץ כאן"‬‎
            <08:14:14> "Amit`": ‎‫ז. מבצע Convert של ה-SQL, שיודע לקרוא כל סוג של SQL מכל סוג של גרסה ולהמיר את התוכן ל-SQL חדש מ-0 של הגרסה החדשה‬‎
            <08:14:19> "Amit`": ‎‫אפשר להוסיף גיבוי גם לשלב ז'‬‎
            <08:14:32> "Amit`": ‎‫מערכת עדכונים מושלמת לכל סוג של עדכון‬‎
            <08:15:07> "Amit`": ‎‫ח. משבית את כל ה-Extentions הלא עדכניים באופן אוטומטי, מי שירצה יוכל לנסות להפעיל אבל יקבל אזהרה שהם לא מעדכנים‬‎

            - Verify files
            - Lockdown system
            - Extract package files
            - Compare current filelist against newest package and apply new files only
            - Alter SQL and work it out
            - Disable all extensions
            - Delete temporary files, wrap up
        */
    }
?>

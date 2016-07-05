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

    define('IN_SYSTEM', true);
    include('../../system.php');

    $token = isset($_GET['token']) ? $_GET['token'] : null;
    $task = isset($_GET['task']) ? $_GET['task'] : null;
    $cmd = isset($_GET['cmd']) ? $_GET['cmd'] : null;

    if(!isset($token) || $token != file_get_contents('background.token') || !isset($task) || !isset($cmd)) exit;

    global $dbg;
    $ret = ['result' => true];

    if($task == 'backup')
    {
        global $session;
        $attr = $session->get('admin_backup_attr');
        $stages = array(
            function()
            {
                global $attr;
                $out = "Initiating backup creation process (`{$attr['type']}`, `{$attr['flag']}`)...";
                return ['result' => true, 'output' => $out];
            },

            function()
            {
                global $attr, $db, $sys;
                $backup = new backup();
                $res = $backup->create($attr['type'], $attr['flag'], $attr['creator'], $attr['notes'], explode(',', $attr['skip']));
                if(!is_numeric($res))
                {
                    $query = $db->prepare('UPDATE `settings` SET `sys_lastbk` = ?');
                    $query->execute([time()]);
                    $sys->sendMessage(1, 'System Backup', 'Backup has been created');
                }
                return ['result' => !is_numeric($res), 'output' => 'Creating a new backup at `'.$res.'`...', 'done' => true];
            }
        );
    }
    elseif($task == 'update')
    {
        $dir = ROOT_DIR . '/_update';
        $stages = array(
            function()
            {
                global $build;
                $latest = parse_ini_string(file_get_contents($build['dev_url'].'latest.ini'));
                $package = ROOT_DIR . "/cms-{$latest['build']}.zip";
                return ['result' => sha1_file($package) == $latest['checksum'], 'output' => 'Verifying package integrity...'];
            },

            function()
            {
                global $dir;
                // shutdown website
                if(file_exists($dir)) delTree($dir);
                mkdir($dir);
                return ['result' => true, 'output' => 'Preparing to update...'];
            },

            function()
            {
                global $build, $dir;
                $latest = parse_ini_string(file_get_contents($build['dev_url'].'latest.ini'));
                $package = ROOT_DIR . "/cms-{$latest['build']}.zip";
                $zip = new ZipArchive;
                $res = $zip->open($package) === true;
                if($res)
                {
                    $zip->extractTo($dir);
                    $zip->close();
                }
                return ['result' => $res, 'output' => 'Extracting build package files...' ];
            },

            function() // rename()
            {
                sleep(5);
                return ['result' => true, 'output' => 'Applying new system files...'];
            },

            function() // learn about dump files and their execution, see if I can make this work
            {
                sleep(5);
                return ['result' => true, 'output' => 'Updating system database...'];
            },

            function()
            {
                global $dir;
                delTree($dir);
                return ['result' => true, 'output' => 'Removing temporary files...'];
            },

            function()
            {
                sleep(5);
                global $sys;
                $sys->sendMessage(1, 'System Update', 'System has been updated'); // bulk message to all sysadmins, get message from lang
                return ['result' => true, 'output' => 'Finishing up...', 'done' => true];
            }
        );
    }

    $ret = array_merge($ret, $cmd == '_' ? ['output' => count($stages)] : call_user_func($stages[$cmd]));
    if(!is_numeric($ret['output'])) $dbg->commit('update', $ret['output']);
    print(json_encode($ret));
?>

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

    // ACP module: system/theme
    if(!IN_SYSTEM) exit;

    if(isset($_POST['apply']) && new theme($_POST['theme']))
    {
        $query = $db->prepare('UPDATE `settings` SET `sys_theme` = ?');
        $query->execute([$_POST['theme']]);
        $settings['sys_theme'] = $_POST['theme'];
        $out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_system_theme_success', $_POST['theme']));
    }
    $themes = array();
    foreach(glob(ROOT_DIR . '/resource/theme/*/manifest.xml') as $th)
    {
        try
        {
            $xml = new SimpleXMLElement(file_get_contents($th));
            $themes[(string)$xml->id] = get_object_vars($xml);
        }
        catch(Exception $e)
        {
            continue;
        }
    }
    $c = $settings['sys_theme'];
    $out .= '<form method="POST" role="form">
                <div class="col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                        </div>
                        <div class="panel-body">
                            '.$lang->get('acp_m_system_theme_info').'
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_theme_thid'), 'id', 'value="'.$themes[$c]['id'].'" disabled');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_theme_thauth'), 'auth', 'value="'.$themes[$c]['author'].'" disabled');
    $out .= '		</div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field',  $lang->get('acp_m_system_theme_thname'), 'name', 'value="'.$themes[$c]['name'].'" disabled');
    $out .= '		</div>

                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_theme_thweb'), 'web', 'value="'.$themes[$c]['website'].'" disabled');
    $out .= '		</div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>'.$lang->get('acp_m_system_theme_browser').'</label>
                        <select class="form-control" size="4" name="theme" id="theme">';
    foreach($themes as $i => $t)
        $out .= '			<option value="'.$i.'"'.($i == $c ? ' selected' : '').'>'.($i == $c ? '[*] ' : '').'['.$i.']: '.$t['name'].'</option>';
    $out .= '
                        </select>
                    </div>
                    <center>
                        <input class="btn btn btn-success" name="apply" type="submit" value="'.$lang->get('acp_m_system_theme_apply').'">
                    </center>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_textarea', $lang->get('acp_m_system_theme_thdesc'), 'desc', $themes[$c]['desc'], 'rows="8" disabled');
    $out .= '		</div>
                </div>
            </form>';
    $out .= "
            <script>
                $('#theme').on('change', function()
                {";
    $js_array = json_encode($themes);
    $out .= "		var themes = ". $js_array . ";
                    var c = $(this).find(':selected').val();
                    $('#id').attr('value', themes[c]['id']);
                    $('#name').attr('value', themes[c]['name']);
                    $('#auth').attr('value', themes[c]['author']);
                    $('#web').attr('value', themes[c]['website']);
                    $('#desc').val(themes[c]['desc']);
                });
            </script>";
?>

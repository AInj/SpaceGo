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

    // ACP module: system/language
    if(!IN_SYSTEM) exit;

    if(isset($_POST['submit']) && file_exists(ROOT_DIR . "/resource/lang/{$_POST['lang']}.xml"))
    {
        $query = $db->prepare('UPDATE `settings` SET `sys_language` = ?');
        $query->execute([$_POST['lang']]);
        $settings['sys_language'] = $_POST['lang'];
        $out .= $theme->dat('acp', 'alert', 'success', $lang->get('acp_m_system_language_success', $_POST['lang']));
    }
    $packs = array();
    foreach(glob(ROOT_DIR . '/resource/lang/*.xml') as $pack)
    {
        try
        {
            $xml = new SimpleXMLElement(file_get_contents($pack));
            $packs[(string)$xml->info->id] = get_object_vars($xml->info);
            $packs[(string)$xml->info->id]['direction'] = $lang->returnDir($packs[(string)$xml->info->id]['direction']);
            $packs[(string)$xml->info->id]['count'] = count(array_slice(get_object_vars($xml->phrases), 1));
        }
        catch(Exception $e)
        {
            continue;
        }
    }
    $c = $settings['sys_language'];
    $out .= '<form method="POST" role="form">
                <div class="col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">'.$lang->get('acp_m_system_info').'</hr>
                        </div>
                        <div class="panel-body">
                            '.$lang->get('acp_m_system_language_info').'
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_language_packid'), 'id', 'value="'.$packs[$c]['id'].'" disabled');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field',  $lang->get('acp_m_system_language_packname'), 'name', 'value="'.$packs[$c]['name'].'" disabled');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_language_packver'), 'ver', 'value="'.$packs[$c]['version'].'" disabled');
    $out .= '		</div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_language_packauth'), 'auth', 'value="'.$packs[$c]['author'].'" disabled');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_language_packweb'), 'web', 'value="'.$packs[$c]['website'].'" disabled');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_language_packcomp'), 'comp', 'value="'.$packs[$c]['comp'].'" disabled');
    $out .= '		</div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_language_packenc'), 'enc', 'value="'.$packs[$c]['encoding'].'" disabled');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_language_packdir'), 'dir', 'value="'.$packs[$c]['direction'].'" disabled');
    $out .= '		</div>
                    <div class="form-group">';
    $out .= $theme->dat('acp', 'form_field', $lang->get('acp_m_system_language_packcount'), 'count', 'value="'.$packs[$c]['count'].'" disabled');
    $out .= '		</div>
                </div>
                <div class="col-lg-5">
                    <div class="form-group">
                        <label>'.$lang->get('acp_m_system_language_browser').'</label>
                        <select class="form-control" size="8" name="lang" id="lang">';
    foreach($packs as $i => $p)
        $out .= '			<option value="'.$i.'"'.($i == $c ? ' selected' : '').'>'.($i == $c ? '[*] ' : '').'['.$i.']: '.$p['name'].'</option>';
    $out .= '
                        </select>
                    </div>
                    <center>
                        <input class="btn btn btn-success" name="submit" type="submit" value="'.$lang->get('acp_m_system_language_select').'">
                    </center>
                </div>
            </form>';
    $out .= "
            <script>
                $('#lang').on('change', function()
                {";
    $js_array = json_encode($packs);
    $out .= "		var packs = ". $js_array . ";
                    var c = $(this).find(':selected').val();
                    $('#id').attr('value', packs[c]['id']);
                    $('#name').attr('value', packs[c]['name']);
                    $('#ver').attr('value', packs[c]['version']);
                    $('#auth').attr('value', packs[c]['author']);
                    $('#web').attr('value', packs[c]['website']);
                    $('#comp').attr('value', packs[c]['comp']);
                    $('#enc').attr('value', packs[c]['encoding']);
                    $('#dir').attr('value', packs[c]['direction']);
                    $('#count').attr('value', packs[c]['count']);
                });
            </script>";
?>

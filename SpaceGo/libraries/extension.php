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

	class extension
	{
		public $extension = null;
		public $data = array();
		public $events = null;

		function __construct($extension, &$lang)
		{
			global $dbg, $sec;
			if(!$val = $this->validate($extension))
			{
				$this->extension = $extension;
				if(file_exists(ROOT_DIR . "/resource/extensions/{$extension}/install.php")) return $this->extension = null;
				foreach(['manifest', 'lang'] as $data)
				{
					$file = ROOT_DIR . "/resource/extensions/{$extension}/{$data}.xml";
					try
					{
						$xml = new SimpleXMLElement(file_get_contents($file));
					}
					catch(Exception $e)
					{
						$dbg->commit('global', "[Extension] File '{$file}' is unreachable");
						return $this->extension = null;
					}
					$this->data[$data] = $xml;
				}
				if(!empty($this->data['lang']))
				{
					$this->data['lang'] = (array)$this->data['lang']->phrases;
					foreach($this->data['lang'] as $k => $v)
					{
						$this->data['lang']['ext_'.$extension.'_'.$k] = $v;
						unset($this->data['lang'][$k]);
					}
					$lang = array_merge($lang, $this->data['lang']);
				}
				if(!empty($perm = $this->data['manifest']->acp_perm)) $sec->perms = array_merge($sec->perms, [explode(',', $perm)]);
				if(file_exists(($inc = ROOT_DIR . "/resource/extensions/{$extension}/events.php"))) $this->events = include($inc);
			}
			else
			{
				$dbg->commit('global', "[Extension] Extension '{$extension}' is invalid, missing {$val} and skipping");
				return $this->extension = null;
			}
		}

		public function validate($extension)
		{
			$files = array(
				'/resource/extensions/_/',
				'/resource/extensions/_/manifest.xml',
				'/resource/extensions/_/lang.xml',
				'/resource/extensions/_/theme.xml'
			);
			foreach(str_replace('/_/', "/{$extension}/", $files) as $file)
				if(!file_exists(ROOT_DIR . $file) || !is_readable(ROOT_DIR . $file))
					return $file;
			return 0;
		}

		public function event()
		{
			if(!$this->extension) return;
			$args = func_get_args();
			if(!$this->events || !function_exists($func = "ext_{$this->extension}_{$args[0]}")) return;
			return call_user_func_array($func, array_slice($args, 1));
		}

		public function acpMenu(&$items)
		{
			if(!$this->extension) return;
			foreach(explode('|', $this->data['manifest']->acp_menu) as $buff)
				$items = array_merge($items, [explode(',', $buff)]);
		}
	}
?>

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

	class language
	{
		public $lang = null;
		public $lang_path = null;
		public $lang_info = array();
		public $lang_phr = array();

		function __construct($lang)
		{
			$this->lang_path = ROOT_DIR . "/resource/lang/{$lang}.xml";
			if(!file_exists($this->lang_path) || !is_readable($this->lang_path))
			{
				global $dbg;
				$dbg->commit('global', "[Language] Package '{$lang}' at '{$this->lang_path}' is unreachable");
				$dbg->halt("lang: unable to reach '{$this->lang_path}'");
				return;
			}
			$this->lang = $lang;
			$this->preload();
		}

		private function preload()
		{
			try
			{
				$xml = new SimpleXMLElement(file_get_contents($this->lang_path));
			}
			catch(Exception $e)
			{
				global $dbg;
				$dbg->xml_handler($e);
			}
			$this->lang_info = array_slice(get_object_vars($xml->info), 1);
			$this->lang_phr = array_slice(get_object_vars($xml->phrases), 1);
		}

		public function get()
		{
			if(!isset($this->lang) || !$args = func_get_args()) return;
			if(!isset($this->lang_phr[$args[0]])) return;
			$bindings = array_slice($args, 1);
			$keys = array_keys($bindings);
			for($i = 0; $i < sizeof($keys); $i++) $keys[$i] = '{'.$i.'}';
			return nl2br(str_replace($keys, $bindings, $this->lang_phr[$args[0]]));
		}

		public function parse($string)
		{
			if(startsWith($string, $r = 'lang::'))
				return $this->get(substr($string, strlen($r)));
			return $string;
		}

		public function themeFormat()
		{
			if(!isset($this->lang)) return;
			$res = $this->lang_phr;
			foreach($res as $k => $v)
			{
				$res['L_'.strtoupper($k)] = nl2br($v);
				unset($res[$k]);
			}
			return $res;
		}

		public function returnDir($dir)
		{
			if($dir == 'ltr') return 'Left-to-Right (LTR)';
			elseif($dir == 'rtl') return 'Right-to-Left (RTL)';
			return 'N/A';
		}

		/*$x = array_combine(
			array_map(function($k){ return 'L_'.strtoupper($k); }, array_keys($x)),
			$x
		);*/
	}
?>

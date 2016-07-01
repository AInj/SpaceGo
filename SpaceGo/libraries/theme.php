<?php
	/*
		TODO: Consider the addition of statements (if-else) for themes
				and consider integrating a full functional theme engine
			http://www.smarty.net/
			http://twig.sensiolabs.org/
	*/
	class theme
	{
		public $minify;
		public $theme = null;
		public $manifest = null;
		public $stack = array();

		function __construct($theme, $minify = false)
		{
			if(!$val = $this->validate($theme))
			{
				$this->minify = $minify;
				$this->manifest = $this->retManifest($this->theme = $theme);
			}
			else
			{
				global $dbg;
				$dbg->commit('global', "[Theme] Theme '{$theme}' is invalid, missing {$val}");
				$dbg->halt("theme: theme '{$theme}' is invalid, missing {$val}");
				return 0;
			}
			return 1;
		}

		public function validate($theme)
		{
			$files = array(
				'/resource/theme/_/',
				'/resource/theme/_/manifest.xml',
				'/resource/theme/_/html/',
			);
			foreach(str_replace('/_/', "/{$theme}/", $files) as $file)
				if(!file_exists(ROOT_DIR . $file) || !is_readable(ROOT_DIR . $file))
					return $file;
			return 0;
		}

		public function retManifest($theme)
		{
			if(!$theme) return;
			global $dbg;
			$file = ROOT_DIR . "/resource/theme/{$theme}/manifest.xml";
			try
			{
				$xml = new SimpleXMLElement(file_get_contents($file));
			}
			catch(Exception $e)
			{
				$dbg->commit('global', "[Theme] File '{$file}' is unreachable");
				$dbg->halt("theme: unable to read manifest");
			}
			return (array)$xml;
		}

		public function load($layer, $bindings = array())
		{
			if(!$this->theme) return;
			global $dbg, $config, $lang;
			$file = ROOT_DIR . "/resource/theme/{$this->theme}/html/{$layer}.html";
			if(!file_exists($file) || !is_readable($file))
			{
				$dbg->commit('global', "[Theme] File '{$file}' is unreachable");
				$dbg->halt("theme: unable to reach '{$file}'");
			}
			$result = file_get_contents($file);
			$bindings = array_merge(
				array('_ROOT' => $config['loc_dir'], '_THEME' => $this->theme),
				$lang ? $lang->themeFormat() : [null], $bindings
			);
			$keys = array_keys($bindings);
			for($i = 0; $i < sizeof($keys); $i++) $keys[$i] = '{{'.$keys[$i].'}}';
			$result = str_replace($keys, $bindings, $result);
			array_push($this->stack, $result);
		}

		public function dat()
		{
			if(!isset($this->theme) || !$args = func_get_args()) return;
			$ext = $args[0] == 'ext';
			$file = ROOT_DIR . ($ext ? "/resource/extensions/{$args[1]}/theme" : "/resource/theme/{$this->theme}/html/dat/{$args[0]}") . '.xml';
			if(!file_exists($file) || !is_readable($file)) return;
			try
			{
				$xml = new SimpleXMLElement(file_get_contents($file));
			}
			catch(Exception $e)
			{
				return;
			}
			$bindings = array_slice($args, $ext ? 3 : 2);
			$keys = array_keys($bindings);
			for($i = 0; $i < sizeof($keys); $i++) $keys[$i] = '{'.$i.'}';
			return str_replace($keys, $bindings, $xml->{$args[$ext ? 2 : 1]});
		}

		public function execute()
		{
			if($this->minify) ob_start(array($this, 'minifier'));
			foreach($this->stack as $line)
				print($line);
			if($this->minify) ob_end_flush();
		}

		private function minifier($buffer)
		{
			return preg_replace(array('/<!--(.*)-->/Uis',"/[[:blank:]]+/"),array('',' '),str_replace(array("\n","\r","\t"),'', $buffer));
		}
	}
?>

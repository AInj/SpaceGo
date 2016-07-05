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

	/*
		https://gist.github.com/eddmann/10262795
		https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions
	*/
	class session
	{
		function __construct()
		{
			session_start();

			if(!$this->get('_session')) $this->generate();
			else
			{
				if($this->get('_session')['_init'] < time() - 300)
				{
					session_regenerate_id(true);
					$this->get('_session')['_init'] = time();
				}
				if($this->get('_session')['_remoteaddr'] != $_SERVER['REMOTE_ADDR'] ||
					$this->get('_session')['_useragent'] != $_SERVER['HTTP_USER_AGENT'])
				{
					unset($_SESSION);
					$this->generate();
				}
			}
		}

		private function generate()
		{
			session_regenerate_id(true);
			$this->set('_session', array(
				'_init'			=>	time(),
				'_remoteaddr'	=>	$_SERVER['REMOTE_ADDR'],
				'_useragent'	=>	$_SERVER['HTTP_USER_AGENT']
			));
		}

		public function set($key, $value)
		{
			$_SESSION[$key] = $value;
		}

		public function rst($key)
		{
			$_SESSION[$key] = null;
		}

		public function get($key)
		{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
		}

		public function destroy()
		{
			session_destroy();
		}
	}
?>

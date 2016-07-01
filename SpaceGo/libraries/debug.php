<?php
	class debug
	{
		public $log = 'file/log'; // https://en.wikipedia.org/wiki/Guru_Meditation
		public $dbLogTypes = ['accounts', 'test'];

		function __construct()
		{
			if(!file_exists(ROOT_DIR . '/' . $this->log))
				$this->halt("Hard-logging folder is missing from `{$this->log}`");
		}

		public function halt($string)
		{
			global $theme;
			$theme = new theme('admin/_error');
			$theme->load('error', ['ERROR' => $string]);
			$theme->execute();
			exit;
		}

		public function commit($log, $string)
		{
			global $env;
			$file = $this->log . '/' . $log . '.log';
			$append = date('[d/m/Y | H:i:s] ') . $string . $env['nl'];
			file_put_contents(ROOT_DIR . '/' . $file, $append, FILE_APPEND | LOCK_EX);
			return $append;
		}

		public function dbLog($log, $entry, $by = 0)
		{
			global $db;
			$query = $db->prepare("INSERT INTO `logs` (`time`, `log`, `entry`, `by`) VALUES (?, ?, ?, ?)");
			$query->execute([time(), $log, $entry, $by]);
		}

		public function php_handler($errno, $errstr, $errfile, $errline)
		{
		}

		public function pdo_handler(PDOException $e)
		{
			$this->commit('db', $e->getMessage());
			$this->halt('DB: ' . $e->getMessage());
		}

		public function xml_handler(Exception $e)
		{
			$this->commit('xml', $e->getMessage());
			$this->halt('XML: ' . $e->getMessage());
		}
	}
?>

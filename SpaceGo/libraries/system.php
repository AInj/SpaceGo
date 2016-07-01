<?php
	class sys
	{
		function __construct(&$settings)
		{
			$this->loadSet($settings);
		}

		public function loadSet(&$settings)
		{
			global $db;
			$query = $db->prepare('SELECT * FROM `settings`');
			$query->execute();
			$settings = $query->fetch(PDO::FETCH_ASSOC);
		}

		public function loadAcc($id, $dat = '*')
		{
			global $db;
			$query = $db->prepare("SELECT {$dat} FROM `accounts` WHERE `id` = ?");
			$query->execute([$id]);
			return $query->fetch(PDO::FETCH_ASSOC);
		}

		public function getUsername($id)
		{
			global $db;
			$query = $db->prepare("SELECT `username` FROM `accounts` WHERE `id` = ?");
			$query->execute([$id]);
			return $query->fetchColumn();
		}

		public function sendMessage($to, $subject, $content, $sender = null)
		{
			global $lang, $db;
			if(is_null($sender)) $sender = $lang->get('glob_sysadm');
			$query = $db->prepare('INSERT INTO `messages` (`to`, `sender`, `subject`, `content`, `time`) VALUES (?, ?, ?, ?, ?)');
			$query->bindParam(1, $to, PDO::PARAM_INT);
			$query->bindParam(2, $sender, PDO::PARAM_STR);
			$query->bindParam(3, $subject, PDO::PARAM_STR);
			$query->bindValue(4, trim(preg_replace('/\t+/', '', strip_tags($content))), PDO::PARAM_STR);
			$query->bindValue(5, time(), PDO::PARAM_STR);
			$query->execute();
		}
	}

	function truncate($string, $length = 100, $append = "&hellip;")
	{
		$string = trim($string);
		if(strlen($string) > $length)
		{
			$string = wordwrap($string, $length);
			$string = explode("\n", $string, 2);
			$string = $string[0] . $append;
		}
		return $string;
	}

	function startsWith($haystack, $needle)
	{
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	function endsWith($haystack, $needle)
	{
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

	function in_array_r($needle, $haystack, $strict = false)
	{
		foreach($haystack as $item)
		{
			if(($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict)))
			{
				return true;
			}
		}
		return false;
	}

	function delTree($dir)
	{
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach($files as $file) (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
		return rmdir($dir);
	}
?>

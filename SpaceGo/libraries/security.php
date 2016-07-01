<?php
	class security
	{
		// Password hashing
		public function pwdHash($passwd)
		{
			global $build;
			return password_hash($passwd, constant($build['sec_pwdhashalgo']), ['cost' => $build['sec_pwdhashcost']]);
		}

		public function pwdVerify($passwd, $hash)
		{
			return password_verify($passwd, $hash);
		}

		// Password standards
		public function pwdStandard($password)
		{
			global $settings;
			return strlen($password) >= $settings['sec_pwdstandard'];
		}

		// Account permissions
		public $perms = array(
			['Accounts',	'_'],
			['View',		'acc'],
			['Create',		'acc_cre'],
			['Modify',		'acc_mod'],
			['Delete',		'acc_del'],

			['System',		'_'],
			['Settings',	'sys_set'],
			['Theme',		'sys_the'],
			['Language',	'sys_lng'],
			['Security',	'sys_sec'],
			['Logs',		'sys_log'],
			['Backups',		'sys_bks'],
			['Extensions',	'sys_ext'],

			['Extensions',	'_']
		);

		public function permission($key, $id = -1)
		{
			global $session, $db;
			if(!($id = $id == -1 ? $session->get('admin_auth') : $id)) return 0;
			if(empty($key)) return 1;
			$query = $db->prepare("SELECT `{$key}` FROM `permissions` WHERE `id` = ?");
			$query->execute([$id]);
			return $query->fetchColumn();
		}

		public function update_perm($id, $val)
		{
			if(empty($val)) $val = array();
			global $db;
			$prep = 'UPDATE `permissions` SET ';
			$i = 0;
			$perms = array();
			foreach($this->perms as $p => $dat) if($dat[1] != '_') $perms[$i++] = $dat;
			$perms_count = count($perms);
			foreach($perms as $p => $dat)
			{
				if($dat[1] == '_') continue;
				$prep .= "`{$dat[1]}` = " . $db->quote((int)in_array($dat[1], $val)) . ($p == $perms_count - 1 ? '' : ', ');
			}
			$prep = $prep . ' WHERE `id` = ?';
			$query = $db->prepare($prep);
			echo $prep;
			$query->execute([$id]);
		}

		// Banning
		public function ban($type)
		{
			global $db;
			$query = $db->prepare("SELECT * FROM `bans` WHERE `ip` = '{$_SERVER['REMOTE_ADDR']}' AND `type` = '{$type}'");
			$query->execute();
			$result = $query->fetch(PDO::FETCH_ASSOC);
			if(($result['time'] + $result['expiry']) < time())
			{
				$query = $db->prepare("DELETE FROM `bans` WHERE `ip` = '{$_SERVER['REMOTE_ADDR']}' AND `type` = '{$type}'");
				$query->execute();
				return 0;
			}
			return $query->rowCount();
		}

		public function banSet($type, $time, $reason)
		{
			global $db;
			$query = $db->prepare("INSERT INTO `bans` (`type`, `ip`, `time`, `expiry`, `reason`) VALUES (?, ?, ?, ?, ?)");
			$query->execute([$type, $_SERVER['REMOTE_ADDR'], $time, (time() + $time), $reason]);
		}
	}
?>

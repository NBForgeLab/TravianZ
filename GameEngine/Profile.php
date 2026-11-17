<?php

class Profile {

	private $database;
	private $session;
	private $form;

	public function __construct($database = null, $session = null, $form = null) {
		$this->database = $database ?: $GLOBALS['database'];
		$this->session = $session ?: $GLOBALS['session'];
		$this->form = $form ?: $GLOBALS['form'];
	}

	private static $cache = [
		'vac_check' => [],
		'villages'  => []
	];

	private function isSitter() {
		$session = $this->session;

		return isset($session) && is_object($session)
			&& method_exists($session, 'isSitterSession')
			&& $session->isSitterSession();
	}

	public function procProfile($post) {
		if ($this->isSitter()) {
			return;
		}

		if (isset($post['ft'])) {
			switch ($post['ft']) {
				case "p1":
					$this->updateProfile($post);
					break;

				case "p2":
					$this->updatePreferences($post);
					break;

				case "p3":
					$this->updateAccount($post);
					break;

				case "p4":
					$this->setvactionmode($post);
					break;
			}
		}

		if (isset($post['s']) && $post['s'] == 4) {
			$this->gpack($post);
		}
	}

	private function updatePreferences($post) {
		$database = $this->database;
		$session = $this->session;

		$v1  = empty($post['v1'])  ? 0 : 1;
		$v2  = empty($post['v2'])  ? 0 : 1;
		$v3  = empty($post['v3'])  ? 0 : 1;
		$map = empty($post['map']) ? 0 : 1;
		$v4  = empty($post['v4'])  ? 0 : 1;
		$v5  = empty($post['v5'])  ? 0 : 1;
		$v6  = empty($post['v6'])  ? 0 : 1;

		$timezone = isset($post['timezone']) ? (int)$post['timezone'] : 1;
		$tformat  = isset($post['tformat'])  ? (int)$post['tformat']  : 0;
		if ($tformat < 0 || $tformat > 3) {
			$tformat = 0;
		}

		$database->query(
			"UPDATE " . TB_PREFIX . "users SET " .
			"v1=$v1, v2=$v2, v3=$v3, map=$map, v4=$v4, v5=$v5, v6=$v6, " .
			"timezone=$timezone, tformat=$tformat " .
			"WHERE id=" . (int)$session->uid
		);

		unset($_SESSION['cache_user_' . ($_SESSION['username'] ?? '')]);

		if (!empty($post['lang'])) {
			$lang = strtolower(trim($post['lang']));
			if (preg_match('/^[a-z]{2}$/', $lang) && is_file(__DIR__ . '/Lang/' . $lang . '.php')) {
				$database->updateUserField($session->uid, "lang", $lang, 1);
				$_SESSION['lang'] = $lang;
				$session->userinfo['lang'] = $lang;
			}
		}

		$this->updateLinks($post);

		header("Location: spieler.php?s=2");
		exit;
	}

	private function updateLinks($post) {
		$database = $this->database;
		$session = $this->session;
		$uid = (int)$session->uid;
		$links = [];
		foreach ($post as $key => $value) {
			if (!is_string($value)) continue;
			$value = trim($value);
			if (strpos($key, 'linkname') === 0) $links[substr($key, 8)]['name'] = $value;
			elseif (strpos($key, 'linkziel') === 0) $links[substr($key, 8)]['url'] = $value;
			elseif (strpos($key, 'nr') === 0) $links[substr($key, 2)]['nr'] = (int)$value;
			elseif (strpos($key, 'id') === 0) $links[substr($key, 2)]['id'] = (int)$value;
		}
		foreach ($links as $link) {
			$nr = isset($link['nr']) ? (int)$link['nr'] : 0;
			$id = isset($link['id']) ? (int)$link['id'] : 0;
			$name = mb_substr(strip_tags($link['name'] ?? ''), 0, 30);
			$url = '';
			if (($link['url'] ?? '') !== '' && preg_match('#^https?://#i', $link['url'])) {
				$url = mb_substr($link['url'], 0, 120);
			}
			$name_sql = mysqli_real_escape_string($database->dblink, $name);
			$url_sql = mysqli_real_escape_string($database->dblink, $url);
			if ($nr !== 0 && $name !== '' && $url !== '' && $id === 0) {
				mysqli_query($database->dblink, "INSERT INTO `" . TB_PREFIX . "links` (`userid`, `name`, `url`, `pos`) VALUES ($uid, '$name_sql', '$url_sql', $nr)");
			} elseif ($nr !== 0 && $name !== '' && $url !== '' && $id > 0) {
				mysqli_query($database->dblink, "UPDATE `" . TB_PREFIX . "links` SET `name`='$name_sql', `url`='$url_sql', `pos`=$nr WHERE `id`=$id AND `userid`=$uid");
			} elseif ($nr === 0 && $name === '' && $url === '' && $id > 0) {
				mysqli_query($database->dblink, "DELETE FROM `" . TB_PREFIX . "links` WHERE `id`=$id AND `userid`=$uid");
			}
		}
	}

	public function procSpecial($get) {
		if ($this->isSitter()) return;
		if (isset($get['e'])) {
			switch ($get['e']) {
				case 2: $this->removeMeSit($get); break;
				case 3: $this->removeSitter($get); break;
				case 4: $this->cancelDeleting($get); break;
			}
		}
	}

	private function updateProfile($post) {
		$database = $this->database;
		$session = $this->session;
		$birthday = ($post['jahr'] ?? '0') . '-' . ($post['monat'] ?? '0') . '-' . ($post['tag'] ?? '0');
		$birthday = preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $birthday) ? $birthday : '0';
		$mw = (int)($post['mw'] ?? 0);
		$ort = trim($post['ort'] ?? '');
		$be1 = trim($post['be1'] ?? '');
		$be2 = trim($post['be2'] ?? '');
		$database->submitProfile($session->uid, $mw, $ort, $birthday, $be2, $be1);
		if (!isset(self::$cache['villages'][$session->uid])) {
			self::$cache['villages'][$session->uid] = $database->getProfileVillages($session->uid);
		}
		$varray = self::$cache['villages'][$session->uid];
		$cnt = count($varray);
		for ($i = 0; $i < $cnt; $i++) {
			if (!isset($post['dname' . $i])) continue;
			$newName = trim($post['dname' . $i]);
			if ($newName === '') continue;
			if (function_exists('mb_strlen')) {
				if (mb_strlen($newName, 'UTF-8') > 25) $newName = mb_substr($newName, 0, 25, 'UTF-8');
			} elseif (strlen($newName) > 25) $newName = substr($newName, 0, 25);
			if (!preg_match('/^[\p{L}\p{N}._\-\[\]()]+(?: [\p{L}\p{N}._\-\[\]()]+)*$/u', $newName)) continue;
			$database->setVillageName($varray[$i]['wref'], $newName);
		}
		unset($_SESSION['cache_user_' . ($_SESSION['username'] ?? '')]);
		header("Location: spieler.php?uid=" . $session->uid);
		exit;
	}

	private function gpack($post) {
		$database = $this->database;
		$session = $this->session;
		$chosen = trim((string) ($post['custom_url'] ?? ''));
		$isLocalPack = (bool) preg_match('#^gpack/[A-Za-z0-9_\-]+/$#', $chosen) && is_file(__DIR__ . '/../' . $chosen . 'travian.css');
		if ($chosen !== '' && !$isLocalPack) {
			header("Location: spieler.php?s=4");
			exit;
		}
		$database->gpack($database->RemoveXSS($session->uid), $database->RemoveXSS($chosen));
		$_SESSION['gpack'] = $chosen;
		unset($_SESSION['cache_user_' . ($_SESSION['username'] ?? '')]);
		header("Location: spieler.php?uid=" . $session->uid);
		exit;
	}

	private function setvactionmode($post) {
		$database = $this->database;
		$session = $this->session;
		if (isset($post['vac']) && $post['vac'] && isset($post['vac_days']) && $post['vac_days'] >= 2 && $post['vac_days'] <= 14) {
			$uid = $session->uid;
			if (!isset(self::$cache['vac_check'][$uid])) self::$cache['vac_check'][$uid] = $database->checkVacationRequirements($uid);
			$check = self::$cache['vac_check'][$uid];
			if ($check !== true) {
				$messages = ["TROOPS_MOVING" => "You still have troops moving", "INCOMING_TROOPS" => "You have incoming troops", "REINFORCEMENTS" => "You have reinforcements on your villages", "WW" => "You own a Wonder of the World", "ARTEFACTS" => "You own artefacts", "PROTECTION" => "You are still under beginner protection", "PRISONERS_IN" => "No units trapped in your traps", "PRISONERS_OUT" => "No units in enemy traps", "MARKET" => "Marketplace transport active", "ACCOUNT_DELETION" => "Account is scheduled for deletion"];
				$output = "";
				foreach ($check as $err) $output .= (isset($messages[$err]) ? $messages[$err] : $err) . "<br>";
				$_SESSION['vac_error'] = $output;
				header("Location: spieler.php?s=5");
				exit;
			}
			unset($_SESSION['wid']);
			$database->setvacmode($uid, $post['vac_days']);
			$database->activeModify(addslashes($session->username), 1);
			$database->UpdateOnline("logout");
			$session->Logout();
			header("Location: login.php");
			exit;
		} else {
			$_SESSION['vac_error'] = "Vacation days must be between 2 and 14";
			header("Location: spieler.php?s=5");
			exit;
		}
	}

	private function updateAccount($post) {
		$database = $this->database;
		$session = $this->session;
		$form = $this->form;
		if (!empty($post['pw1']) && !empty($post['pw2']) && !empty($post['pw3'])) {
			if ($post['pw2'] == $post['pw3']) {
				if ($database->login($session->username, $post['pw1'])) {
					$database->updateUserField($session->uid, "password", password_hash($post['pw2'], PASSWORD_BCRYPT, ['cost' => 12]), 1);
				} else {
					$form->addError("pw", LOGIN_PW_ERROR);
				}
			} else {
				$form->addError("pw", PASS_MISMATCH);
			}
		}
		if (!empty($post['email_alt']) && !empty($post['email_neu'])) {
			if ($post['email_alt'] == $session->userinfo['email']) {
				$database->updateUserField($session->uid, "email", $post['email_neu'], 1);
			} else {
				$form->addError("email", EMAIL_ERROR);
			}
		}
		if (!empty($post['del_pw']) && !empty($post['del'])) {
			if (password_verify($post['del_pw'], $session->userinfo['password'])) {
				$database->setDeleting($session->uid, 0);
			} else {
				$form->addError("del", PASS_MISMATCH);
			}
		}
		$sitterMaskFromPost = function ($raw) {
			$mask = 0;
			if (is_array($raw)) {
				foreach ($raw as $bit) {
					$bit = (int) $bit;
					if ($bit > 0 && ($bit & SITTER_PERM_ALL) === $bit) $mask |= $bit;
				}
			}
			return $mask;
		};
		if (!empty($post['v1'])) {
			$sitid = $database->getUserField($post['v1'], "id", 1);
			if ($sitid == $session->userinfo['sit1'] || $sitid == $session->userinfo['sit2']) {
				$form->addError("sit", SIT_ERROR);
			} else if ($sitid != $session->uid) {
				$newPerm = $sitterMaskFromPost($post['perm_new'] ?? null);
				if ($session->userinfo['sit1'] == 0) {
					$database->updateUserField($session->uid, "sit1", $sitid, 1);
					$database->updateUserField($session->uid, "sit1_perm", $newPerm, 1);
				} else if ($session->userinfo['sit2'] == 0) {
					$database->updateUserField($session->uid, "sit2", $sitid, 1);
					$database->updateUserField($session->uid, "sit2_perm", $newPerm, 1);
				}
			}
		}
		foreach (array(1 => 'sit1', 2 => 'sit2') as $slot => $key) {
			if ((int) $session->userinfo[$key] === 0) continue;
			$field = 'perm' . $slot;
			if (!isset($post[$field . '_sent'])) continue;
			$database->updateUserField($session->uid, $key . '_perm', $sitterMaskFromPost($post[$field] ?? null), 1);
		}
		if ($form->returnErrors() > 0) {
			$_SESSION['errorarray'] = $form->getErrors();
			$_SESSION['valuearray'] = $_POST;
		}
		unset($_SESSION['cache_user_' . ($_SESSION['username'] ?? '')]);
		header("Location: spieler.php?s=3");
		exit;
	}

	private function removeSitter($get) {
		$database = $this->database;
		$session = $this->session;
		$type = isset($get['type']) ? (int)$get['type'] : 0;
		if ($type !== 1 && $type !== 2) {
			header("Location: spieler.php?s=" . $get['s']);
			exit;
		}
		if (hash_equals((string) $session->checker, (string) ($get['a'] ?? ''))) {
			if ($session->userinfo['sit' . $type] == $get['id']) {
				$database->updateUserField($session->uid, "sit" . $type, 0, 1);
				unset($_SESSION['cache_user_' . ($_SESSION['username'] ?? '')]);
			}
			$session->changeChecker();
		}
		header("Location: spieler.php?s=" . $get['s']);
		exit;
	}

	private function cancelDeleting($get) {
		$database = $this->database;
		$database->setDeleting($this->session->uid, 1);
		header("Location: spieler.php?s=" . $get['s']);
		exit;
	}

	private function removeMeSit($get) {
		$database = $this->database;
		$session = $this->session;
		if (hash_equals((string) $session->checker, (string) ($get['a'] ?? ''))) {
			$database->removeMeSit($get['id'], $session->uid);
			$session->changeChecker();
		}
		header("Location: spieler.php?s=" . $get['s']);
		exit;
	}
}
$profile = new Profile();

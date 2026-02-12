<?php



class Profile {
	public function procProfile($post) {
		global $session;
		
		if(isset($post['ft'])) {
			switch($post['ft']) {
				case "p1" :
					$this->updateProfile($post);
					break;
				case "p3" :
					$this->updateAccount($post);
					break;
				case "p4" :
					$this->setvactionmode($post);
					break;
			}
		}
		
		if(isset($post['s']) && $post['s'] == 4) $this->gpack($post);
	}

	public function procSpecial($get) {
		global $session;
		
		if(isset($get['e'])) {
			switch($get['e']) {
				case 2 :
					$this->removeMeSit($get);
					break;
				case 3 :
					$this->removeSitter($get);
					break;
				case 4 :
					$this->cancelDeleting($get);
					break;
			}
		}
	}

	private function updateProfile($post) {
		global $database, $session;
		
		$birthday = $post['jahr'].'-'.$post['monat'].'-'.$post['tag'];
		$database->submitProfile($session->uid, $database->RemoveXSS($post['mw']), $database->RemoveXSS($post['ort']), $database->RemoveXSS($birthday), $database->RemoveXSS($post['be2']), $database->RemoveXSS($post['be1']));
		$varray = $database->getProfileVillages($session->uid);
		
		for($i = 0; $i < count($varray); $i++){
			$database->setVillageName($varray[$i]['wref'], $database->RemoveXSS(trim($post['dname'.$i])));
		}
		
		header("Location: spieler.php?uid=".$session->uid);
		exit;
	}

	private function gpack($post) {
		global $database, $session;
		
		$database->gpack($database->RemoveXSS($session->uid),$database->RemoveXSS($post['custom_url']));
		header("Location: spieler.php?uid=".$session->uid);
		exit;
	}
	
	/**
	 * Function to vacation mode - by advocaite and Shadow
	 * 
	 * @param array $post The $_POST array
	 */

	private function setvactionmode($post){
	    global $database, $session, $form;

	    $vacDays = isset($post['vac_days']) ? (int) $post['vac_days'] : 0;
	    if(isset($post['vac']) && $post['vac'] && $vacDays >= 2 && $vacDays <= 14){        
	        unset($_SESSION['wid']);
			$database->setvacmode($session->uid, $vacDays);
			$database->activeModify(addslashes($session->username), 1);
			$database->UpdateOnline("logout");
			$session->Logout();
			header("Location: login.php");
			exit;
	    }else{
	    	$form->addError("vac", VAC_MODE_WRONG_DAYS);
	        header("Location: spieler.php?s=".$session->uid);        
	        exit;
	    }
	    
	}

	/**
	 * Function to vacation mode - by advocaite and Shadow
	 * 
	 * @param array $post The $_POST array
	 */

	private function updateAccount($post) {
		global $database, $session, $form;

		if(!empty($post['pw1']) && !empty($post['pw2']) && !empty($post['pw3'])){
			$pw1 = (string) $post['pw1'];
			$pw2 = (string) $post['pw2'];
			$pw3 = (string) $post['pw3'];
			if($pw2 === $pw3){
				if($database->login($session->username, $pw1)){
					$database->updateUserField($session->uid, "password", trz_password_hash($pw2), 1);
				}
				else $form->addError("pw", LOGIN_PW_ERROR);
			}
			else $form->addError("pw", PASS_MISMATCH);
		}

		if(!empty($post['email_alt']) && !empty($post['email_neu'])){
			$emailAlt = (string) $post['email_alt'];
			$emailNew = (string) $post['email_neu'];
			if($emailAlt === $session->userinfo['email']){
				$database->updateUserField($session->uid, "email", $emailNew, 1);
			}
			else $form->addError("email", EMAIL_ERROR);
		}
		
		if(!empty($post['del_pw']) && !empty($post['del'])){
			$delPw = (string) $post['del_pw'];
			if(password_verify($delPw, $session->userinfo['password'])){
				$database->setDeleting($session->uid, 0);
			}
			else $form->addError("del", PASS_MISMATCH);	
		}
		
		if(!empty($post['v1'])){
			$sitid = $database->getUserField((string) $post['v1'], "id", 1);
			if($sitid == $session->userinfo['sit1'] || $sitid == $session->userinfo['sit2']){
				$form->addError("sit", SIT_ERROR);
			}else if($sitid != $session->uid){
				if($session->userinfo['sit1'] == 0){
					$database->updateUserField($session->uid, "sit1", $sitid, 1);
				}else if($session->userinfo['sit2'] == 0){
					$database->updateUserField($session->uid, "sit2", $sitid, 1);
				}
			}
		}
		
		if($form->returnErrors() > 0){
			$_SESSION['errorarray'] = $form->getErrors();
			$_SESSION['valuearray'] = $_POST;
		}	
		
		header("Location: spieler.php?s=3");
		exit;
	}

	private function removeSitter($get) {
		global $database,$session;

		if(isset($get['a']) && $get['a'] == $session->checker) {
			$type = isset($get['type']) ? (int) $get['type'] : 0;
			$id = isset($get['id']) ? (int) $get['id'] : 0;
			if($type === 1 || $type === 2){
				if($session->userinfo['sit'.$type] == $id) {
					$database->updateUserField($session->uid,"sit".$type,0,1);
				}
			}
			$session->changeChecker();
		}

		header("Location: spieler.php?s=".$get['s']);
		exit;
	}

	private function cancelDeleting($get) {
		global $database, $session;
		
		$database->setDeleting($session->uid,1);
		header("Location: spieler.php?s=".$get['s']);
		exit;
	}

	private function removeMeSit($get) {
		global $database, $session;

		if(isset($get['a']) && $get['a'] == $session->checker) {
			$id = isset($get['id']) ? (int) $get['id'] : 0;
			if ($id > 0) {
				$database->removeMeSit($id,$session->uid);
			}
			$session->changeChecker();
		}

		header("Location: spieler.php?s=".$get['s']);
		exit;
	}
};

$profile = new Profile;
?>

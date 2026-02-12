<?php
use App\Entity\User;



global $autoprefix;

// go max 5 levels up - we don't have folders that go deeper than that
$autoprefix = '';
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix.'autoloader.php')) {
        // we have our path, let's leave
        break;
    }
}

include_once($autoprefix."GameEngine/Session.php");
/** @var Session $session */
/** @var MYSQLi_DB $database */

class Account {

	function __construct() {
		global $session;
		/** @var Session $session */
		if(isset($_POST['ft'])) {
			switch($_POST['ft']) {
				case "a1":
				$this->Signup();
				break;
				case "a2":
				$this->Activate();
				break;
				case "a3":
				$this->Unreg();
				break;
				case "a4":
				$this->Login();
				break;
			}
		} if(isset($_GET['code'])) {
		$_POST['id'] = $_GET['code']; $this->Activate();
		}
		else {
			if($session->logged_in && in_array("logout.php",explode("/",$_SERVER['PHP_SELF']))) {
				$this->Logout();
			}
		}
	}

	private function Signup() {
		global $database,$form,$mailer,$generator;
		$dbc = $database;
        $post_name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
        $post_pw   = isset($_POST['pw']) ? (string) $_POST['pw'] : '';
        $post_email= isset($_POST['email']) ? (string) $_POST['email'] : '';
        $post_vid  = isset($_POST['vid']) ? (int) $_POST['vid'] : 0;
        $post_kid  = isset($_POST['kid']) ? (int) $_POST['kid'] : 0;
        $post_agb  = isset($_POST['agb']);
        $post_invited = isset($_POST['invited']) ? (string) $_POST['invited'] : '';
        if (!isset($_SESSION['csrf_reg']) || !isset($_POST['csrf']) || $_SESSION['csrf_reg'] !== $_POST['csrf']) {
            $form->addError("agree","Security check failed");
            $_SESSION['errorarray'] = $form->getErrors();
            $_SESSION['valuearray'] = $_POST;
            header("Location: anmelden.php");
            exit;
        }
		if($post_name === "") {
			$form->addError("name",USRNM_EMPTY);
		}
		else {
			if(strlen($post_name) < USRNM_MIN_LENGTH) {
				$form->addError("name",USRNM_SHORT);
			}
			else if(!USRNM_SPECIAL && preg_match('/[^0-9A-Za-z]/',$post_name)) {
				$form->addError("name",USRNM_CHAR);
			}
			else if(USRNM_SPECIAL && preg_match("/[:,\\. \\n\\r\\t\\s\\<\\>]+/", $post_name)) {
				$form->addError("name",USRNM_CHAR);
			}
			else if(strtolower($post_name) == 'natars') {
                $form->addError("name",USRNM_TAKEN);
            }
			else if(User::exists($dbc,$post_name)) {
				$form->addError("name",USRNM_TAKEN);
			}

		}
		if($post_pw === "") {
			$form->addError("pw",PW_EMPTY);
		}
		else {
			if(strlen($post_pw) < PW_MIN_LENGTH) {
				$form->addError("pw",PW_SHORT);
			}
			else if($post_pw === $post_name) {
				$form->addError("pw",PW_INSECURE);

			}
		}
		if($post_email === "") {
			$form->addError("email",EMAIL_EMPTY);
		}
		else {
			if(!$this->validEmail($post_email)) {
				$form->addError("email",EMAIL_INVALID);
			}
			else if(User::exists($dbc,$post_email)) {
				$form->addError("email",EMAIL_TAKEN);
			}
		}
		if($post_vid < 1 || $post_vid > 3) {
			$form->addError("tribe",TRIBE_EMPTY);
		}
		if(!$post_agb) {
			$form->addError("agree",AGREE_ERROR);
		}
		if($form->returnErrors() > 0) {
            $form->addError("invt",$post_invited);
            $_SESSION['errorarray'] = $form->getErrors();
            $_SESSION['valuearray'] = $_POST;


            header("Location: anmelden.php");
            exit;
        }
		else {
			if(AUTH_EMAIL){
			$act = $generator->generateRandStr(10);
			$act2 = $generator->generateRandStr(5);
			$uid = $database->activate($post_name, trz_password_hash($post_pw),$post_email,$post_vid,$post_kid,$act,$act2);
				if($uid) {

					$mailer->sendActivate($post_email,$post_name,$act);
					header("Location: activate.php?id=$uid&q=$act2");
					exit;
				}
			}
			else {
			    $act = "";
			    $uid = $database->register($post_name, trz_password_hash($post_pw), $post_email, $post_vid, $act);
				if($uid) {
					setcookie("COOKUSR" , $post_name, time() + COOKIE_EXPIRE,COOKIE_PATH);
					setcookie("COOKEMAIL" , $post_email, time() + COOKIE_EXPIRE,COOKIE_PATH);
					$database->updateUserField(
						$uid,
                        ["act", "invited"],
                        ["", $post_invited],
                        1
                    );

					$this->generateBase($post_kid, $uid, $post_name);
					header("Location: login.php");
					exit;
				}
			}
		}
	}

	private function Activate() {
	    global $database;
	    
	    if(START_DATE < date('d.m.Y') or START_DATE == date('d.m.Y') && START_TIME <= date('H:i'))
	    {
	        $rows = $database->query_new(
	            "SELECT act, username, password, email, tribe, location FROM ".TB_PREFIX."activate WHERE act = ? LIMIT 1",
	            $database->escape($_POST['id'])
	        );
	        $dbarray = (is_array($rows) && count($rows)) ? $rows[0] : null;
	        if($dbarray['act'] == $_POST['id']) {
	            $uid = $database->register($dbarray['username'], $dbarray['password'], $dbarray['email'], $dbarray['tribe'], "");
	            if($uid) {
	                $database->unreg($dbarray['username']);
	                $this->generateBase($dbarray['location'],$uid,$dbarray['username']);
	                header("Location: activate.php?e=2");
	                exit;
	            }
	        }
	        else
	        {
	            header("Location: activate.php?e=3");
	            exit;
	        }
	    }
	    else
	    {
	        header("Location: activate.php");
	        exit;
	    }
	}

	private function Unreg() {
		global $database;
		
		$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
		$rows = $database->query_new("SELECT password, username FROM ".TB_PREFIX."activate WHERE id = ? LIMIT 1", $id);
		$dbarray = (is_array($rows) && count($rows)) ? $rows[0] : null;
		$pw = isset($_POST['pw']) ? (string) $_POST['pw'] : '';
		if(password_verify($pw, $dbarray['password'])) {
			$database->unreg($dbarray['username']);
			header("Location: anmelden.php");
			exit;
		}
		else {
			header("Location: activate.php?e=3");
			exit;
		}
	}

	private function Login() {
		global $database, $session, $form;
		/** @var MYSQLi_DB $database */
		/** @var Session $session */
		/** @var Form $form */
		
		$user = isset($_POST['user']) ? (string) $_POST['user'] : '';
		$userData = $database->getUserArray($user, 0);
		if($user === ''){
			$form->addError("user", $user);
		}else if(!User::exists($database, $user)){
			$form->addError("user", USR_NT_FOUND);
		}
		$pw = isset($_POST['pw']) ? (string) $_POST['pw'] : '';
		if($pw === ''){
			$form->addError("pw", LOGIN_PASS_EMPTY);
		}else if(!$database->login($user, $pw) && !$database->sitterLogin($user, $pw)){
			// try activation data if the user was not found
			if(!$userData){
				$activateData = $database->getActivateField($user, 'act', 1);
				
				if(!empty($activateData)) $form->addError("activate", $_POST['user']);
				
				else $form->addError("pw", LOGIN_PW_ERROR);
			}
			else $form->addError("pw", LOGIN_PW_ERROR);
		}

			
		// Vacation mode by Shadow
		if($userData["vac_mode"] == 1 && $userData["vac_time"] > time()){
			$form->addError("vacation", "Vacation mode is still enabled");
		}
		
		// Vacation mode by Shadow
		if($form->returnErrors() > 0){
			$_SESSION['errorarray'] = $form->getErrors();
			$_SESSION['valuearray'] = $_POST;
			
			header("Location: login.php");
			exit();
		}else{
			// Vacation mode by Shadow
			$database->removevacationmode($userData['id']);
			// Vacation mode by Shadow
			if($database->login($user, $pw)){
				$database->UpdateOnline("login", $user, time(), $userData['id']);
			}else if($database->sitterLogin($user, $pw)){
				$database->UpdateOnline("sitter", $user, time(), $userData['id']);
			}
			setcookie("COOKUSR", $user, time() + COOKIE_EXPIRE, COOKIE_PATH);
			$session->login($user);
		}
	}

	private function Logout() {
		global $session, $database;
		/** @var Session $session */
		/** @var MYSQLi_DB $database */
		
		unset($_SESSION['wid']);
		$database->activeModify(addslashes($session->username),1);
		$database->UpdateOnline("logout");
		$session->Logout();
	}

	private function validEmail($email) {
	  $regexp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
	  return preg_match($regexp, $email);
	}

	function generateBase($kid, $uid, $username) {
		global $database;
		$message = new Message();
		
		if($kid == 0) $kid = rand(1,4);
		else $kid = $_POST['kid'];
		
		$database->generateVillages([['wid' => 0, 'mode' => 0, 'type' => 3, 'kid' => $kid, 'capital' => 1, 'pop' => 2, 'name' => null, 'natar' => 0]], $uid, $username);
		$message->sendWelcome($uid, $username);
	}
};
$account = new Account;
?>

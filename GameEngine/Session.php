<?php
use App\Entity\User;

if(PHP_VERSION_ID < 80200) {
    http_response_code(500);
    exit('This script requires PHP 8.2+');
}

ob_start(); // Enesure, that no more header already been sent error not showing up again
if(function_exists('mb_internal_encoding')) {
    mb_internal_encoding("UTF-8");
}



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

if(!file_exists($autoprefix.'GameEngine/config.php')) {
    header("Location: install/");
    exit;
}

$script_name = ($_SERVER['REQUEST_URI'] == 'karte.php') ? 'karte' : $_SERVER['REQUEST_URI'];
include_once ("Battle.php");
include_once ("Data/buidata.php");
include_once ("Data/cp.php");
include_once ("Data/cel.php");
include_once ("Data/resdata.php");
include_once ("Data/unitdata.php");
include_once ("Data/hero_full.php");
include_once ("config.php");
include_once ("Database.php");
include_once ("Mailer.php");
include_once ("Form.php");
include_once ("Generator.php");
include_once ("Multisort.php");
include_once ("Ranking.php");
include_once (__DIR__ . "/Lang/" . LANG . ".php");
include_once ("Logging.php");
include_once ("Message.php");
include_once ("Alliance.php");
include_once ("Profile.php");

class Session {

			private $time;
			var $logged_in = false;
			var $referrer, $url;
			var $username, $uid, $access, $plus, $tribe, $isAdmin, $alliance, $gold, $oldrank, $gpack, $goldclub;
			var $bonus = 0;
			var $bonus1 = 0;
			var $bonus2 = 0;
			var $bonus3 = 0;
			var $bonus4 = 0;
			var $timer = 0;
			var $sharedForums = [];
			var $checker, $mchecker;
			public $userinfo = [];
			private $userarray = [];
			var $villages = [];
			var $sit = 0;
            var $sit1 = 0;
            var $sit2 = 0;
            var $cp = 0;

			function __construct() {
        		global $database; //TienTN fix

				$this->time = time();
				if (!isset($_SESSION)) {
					session_start();
				}

				$this->logged_in = $this->checkLogin();

				if($this->logged_in && TRACK_USR) $database->updateActiveUser($this->username, $this->time);
				
				if(isset($_SESSION['url'])) $this->referrer = $_SESSION['url'];
				else $this->referrer = "/";
				
				$this->url = $_SESSION['url'] = $_SERVER['PHP_SELF'];
				$this->SurfControl();
			}

			public function Login($user) {
				global $database, $generator, $logging;
				
				$this->logged_in = true;
				$_SESSION['sessid'] = $generator->generateRandID();
				$_SESSION['username'] = $user;
				$user_sanitized = $database->escape($user);
                if (class_exists(\App\Legacy\SessionHelper::class) || class_exists(\App\Legacy\FormService::class) || travianz_form_try_require_autoloader()) {
                    \App\Legacy\SessionHelper::putString('checker', $generator->generateRandStr(3));
                    \App\Legacy\SessionHelper::putString('mchecker', $generator->generateRandStr(5));
                } else {
                    $_SESSION['checker'] = $generator->generateRandStr(3);
                    $_SESSION['mchecker'] = $generator->generateRandStr(5);
                }

                $userFields = $database->getUserFields($user_sanitized, "quest, id", 1, true);
				$_SESSION['qst'] = $userFields["quest"];

				$dbarray = $database->getUserFields($user_sanitized, 'id, village_select', 1);
				$selected_village=(int) $dbarray['village_select'];
				$_SESSION['id_user'] = (int) $dbarray['id'];

				if ($dbarray['id'] > 1) {
                    if(!isset($_SESSION['wid'])) {
                    	if(!empty($selected_village)) $data = $database->getVillage($selected_village);
                        else $data = $database->getVillage($userFields["id"], 2);
                        $_SESSION['wid'] = $data['wref'];
                    } else
                        if(empty($_SESSION['wid'])) {
                        	if(!empty($selected_village)) $data = $database->getVillage($selected_village);
                            else $data = $database->getVillage($userFields["id"], 2);
                            $_SESSION['wid'] = $data['wref'];
                        }
    				$this->PopulateVar();

    				$database->updateActiveUser($user_sanitized, $this->time);
    				$database->updateUserField($user_sanitized, "sessid", $_SESSION['sessid'], 0);
                }

                $logging->addLoginLog($dbarray['id'], $_SERVER['REMOTE_ADDR']);

                if ($dbarray['id'] == 1) {
				    header("Location: nachrichten.php");
				    exit;
				} else {
				    header("Location: dorf1.php");
				    exit;
				}
			}

			public function Logout() {
				global $database;
				$this->logged_in = false;
				$database->updateUserField($_SESSION['username'], "sessid", "", 0);
				if(ini_get("session.use_cookies")) {
					$params = session_get_cookie_params();
					setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
				}
				session_destroy();
				session_start();
			}

			public function changeChecker() {
				global $generator;
				
                if (class_exists(\App\Legacy\SessionHelper::class) || class_exists(\App\Legacy\FormService::class) || travianz_form_try_require_autoloader()) {
                    $this->checker = $generator->generateRandStr(3);
                    $this->mchecker = $generator->generateRandStr(5);
                    \App\Legacy\SessionHelper::putString('checker', $this->checker);
                    \App\Legacy\SessionHelper::putString('mchecker', $this->mchecker);
                } else {
                    $this->checker = $_SESSION['checker'] = $generator->generateRandStr(3);
                    $this->mchecker = $_SESSION['mchecker'] = $generator->generateRandStr(5);
                }
			}

			private function checkLogin(){
        		global $database;
        		
       		$user = $id = '';
       		$admin = false;
       		$inAdmin = (strpos($_SERVER['REQUEST_URI'], '/Admin') !== false);

       		// إذا حاولنا دخول لوحة الإدارة بدون جلسة إدارية، ولكن لدينا جلسة مستخدم عادي،
       		// قم بترقية الجلسة تلقائياً إن كان للمستخدم صلاحيات إدارية (MULTIHUNTER أو أعلى)
       		if ($inAdmin && !isset($_SESSION['admin_username']) && isset($_SESSION['username']) && isset($_SESSION['id_user'])) {
       		    $possibleId = (int) $_SESSION['id_user'];
       		    $accessLevel = (int) $database->getUserField($possibleId, 'access', 0);
       		    if ($accessLevel >= MULTIHUNTER) {
       		        $_SESSION['admin_username'] = $_SESSION['username'];
       		        $_SESSION['id'] = $possibleId;
       		        $_SESSION['access'] = $accessLevel;
       		    }
       		}

       		if (!$inAdmin && isset($_SESSION['username'])) {
       		    $user = $_SESSION['username'];
       		    $id   = (int) $_SESSION['id_user'];
       		} else if ($inAdmin && isset($_SESSION['admin_username'])) {
       		    $user  = $_SESSION['admin_username'];
       		    $id    = (int) $_SESSION['id'];
       		    $admin = true;
       		}

        		if($user && ($admin || isset($_SESSION['sessid']))) {        		    
        		    $this->maintenance();
        		    $this->isWinner();
        			
        		    // check if this is not a support user, for who only messages and statistics are available
        		    if ($user == 'Support') {
        		        $req_file = basename($_SERVER['PHP_SELF']);
        		        if (!in_array($req_file, ['nachrichten.php', 'logout.php', 'statistiken.php', 'rules.php', 'karte.php', 'karte2.php', 'spieler.php'])) {
        		            header('Location: nachrichten.php');
        		            exit;
        		        }
        		    }

        			//Get and Populate Data
        			$this->PopulateVar();
        			
        			//Check if the player is banned
        			$this->isBanned();
        			
        			//update database
        			$database->updateActiveUser($user, $this->time);
            		return true;
        		} 
        		else return false;
    		}

    		/**
    		 * Called if the player is banned
    		 *
    		 */
    		
    		function isBanned(){
    		    if($this->access == BANNED && !in_array(basename($_SERVER['PHP_SELF']), ['banned.php', 'nachrichten.php', 'rules.php'])){
    		        header('Location: banned.php');
    		        exit;
    		    }
    		}
    		
    		/**
    		 * Called when the server is under maintenance
    		 * 
    		 */
    		
    		function maintenance(){
    		    if($_SESSION['ok'] == 2 && basename($_SERVER['PHP_SELF']) != 'maintenance.php'){
    		        header('Location: maintenance.php');
    		        exit;
    		    }
    		}
    		
    		/**
    		 * Called when there's a player who built a WW to level 100
    		 * 
    		 */
    		
    		function isWinner(){
    			global $database;
    			
    			$requiredPage = basename($_SERVER['PHP_SELF']);
    			if($database->isThereAWinner() && (in_array($requiredPage, ['build.php', 'plus1.php']) || 
    			  (in_array($requiredPage, ['plus.php']) && isset($_GET['id']) && !empty($_GET['id'] && $_GET['id'] >= 7))))
    			{
    				header('Location: winner.php');
    				exit;
    			} 			
    		}
    		
			/**
			 * Function to check Real Hero
			 * Made by: Shadow and brainiacX
			 * 
			 */

 			function CheckHeroReal () {
				global $database,$link;

				$villageIDs = implode(', ', $this->villages);
				if (!count($this->villages)) {
				    $this->Logout();
				    header('login.php');
				    exit;
                }

				// check if hero unit for this player is present anywhere on the map
			    $q = '
                      SELECT
                        IFNULL((SELECT SUM(hero) from '.TB_PREFIX.'enforcement where `from` IN('.$villageIDs.')), 0) +
                        IFNULL((SELECT SUM(hero) from '.TB_PREFIX.'units where `vref` IN('.$villageIDs.')), 0) +
                        IFNULL((SELECT SUM(t11) from '.TB_PREFIX.'prisoners where `from` IN('.$villageIDs.')), 0) +
                        IFNULL((SELECT SUM(t11) FROM '.TB_PREFIX.'movement, '.TB_PREFIX.'attacks WHERE '.TB_PREFIX.'movement.`from` IN('.$villageIDs.') and '.TB_PREFIX.'movement.ref = '.TB_PREFIX.'attacks.id and '.TB_PREFIX.'movement.proc = 0 and '.TB_PREFIX.'movement.sort_type = 3), 0) +
                        IFNULL((SELECT SUM(t11) FROM '.TB_PREFIX.'movement, '.TB_PREFIX.'attacks where '.TB_PREFIX.'movement.`to` IN('.$villageIDs.') and '.TB_PREFIX.'movement.ref = '.TB_PREFIX.'attacks.id and '.TB_PREFIX.'movement.proc = 0 and '.TB_PREFIX.'movement.sort_type = 4), 0)
                        as herocount';
                $rows = $database->query_return($q);
                $heroUnitRegisters = (is_array($rows) && count($rows)) ? (int)($rows[0]['herocount'] ?? 0) : 0;

   				// check if the actual hero is alive or being trained/revived into a living state
                $isHeroLivingOrRaising = $database->getHeroDeadReviveOrInTraining($this->uid);

                // if he doesn't register anywhere on the map but is marked as alive,
                // we need to kill him
                if(!$heroUnitRegisters && $isHeroLivingOrRaising) {
                    $database->KillMyHero($this->uid);
                }
            }

			private function PopulateVar() {
				global $database, $generator;
				
				$this->userarray = $this->userinfo = $database->getUserArray($_SESSION['username'], 0);
				$this->username = $this->userarray['username'];
				$this->uid = $_SESSION['id_user'] =  $this->userarray['id'];
				$this->gpack = $this->userarray['gpack'];
				$this->access = $this->userarray['access'];
				$this->plus = ($this->userarray['plus'] > $this->time);
				$this->goldclub = $this->userarray['goldclub'];
				$this->villages = $database->getVillagesID($this->uid);
				$this->tribe = $this->userarray['tribe'];
				$this->isAdmin = $this->access >= MODERATOR;
				$this->alliance = $_SESSION['alliance_user'] = $this->userarray['alliance'];
				if(!isset($_SESSION['checker']) || $_SESSION['checker'] === '') {
                    $_SESSION['checker'] = $generator->generateRandStr(3);
                }
				if(!isset($_SESSION['mchecker']) || $_SESSION['mchecker'] === '') {
                    $_SESSION['mchecker'] = $generator->generateRandStr(5);
                }
				$this->checker = $_SESSION['checker'];
				$this->mchecker = $_SESSION['mchecker'];
				$this->sit = $database->GetOnline($this->uid);
				$this->sit1 = $this->userarray['sit1'];
				$this->sit2 = $this->userarray['sit2'];
				$this->cp = floor($this->userarray['cp']);
				$this->gold = $this->userarray['gold'];
				$this->oldrank = $this->userarray['oldrank'];
				$this->sharedForums = $database->getSharedForums($this->uid, $this->alliance);
				$_SESSION['ok'] = $this->userarray['ok'];
				
				if($this->userarray['b1'] > $this->time) $this->bonus1 = 1;
				if($this->userarray['b2'] > $this->time) $this->bonus2 = 1;
				if($this->userarray['b3'] > $this->time) $this->bonus3 = 1;
				if($this->userarray['b4'] > $this->time) $this->bonus4 = 1;

				if (!in_array($this->username, ['Support', 'Multihunter'])) $this->CheckHeroReal();
			}
			
			/**
			 * Creates an array with the vrefs of attacked/scouted/reinforced villages and oasis
			 * 
			 */
			
			public function populateAttacks(){
		        global $database, $village;
		        
		        $troopsMovement = $database->getMovement(3, $village->wid, 0);    
		        if(count($troopsMovement) > 0){
		            foreach($troopsMovement as $movement)
		            {
		                switch($movement['attack_type']){
		                    case 1:
		                        $_SESSION['troops_movement']['scouts'][] = $movement['to'];
		                        break;
		                    case 2:
		                        $_SESSION['troops_movement']['enforcements'][] = $movement['to'];
		                        break;
		                    case 3:
		                    case 4:
		                        $_SESSION['troops_movement']['attacks'][] = $movement['to'];
		                        break;
		                }
		            }
		        }	        	    
			}
			
			private function SurfControl(){
				if(SERVER_WEB_ROOT) {
					$page = $_SERVER['SCRIPT_NAME'];
				} else {
					$explode = explode("/", $_SERVER['SCRIPT_NAME']);
					$i = count($explode) - 1;
					$page = $explode[$i];

				}
				$pagearray = array("index.php", "anleitung.php", "tutorial.php", "login.php", "activate.php", "anmelden.php", "xaccount.php");
				if(!$this->logged_in) {
					if(!in_array($page, $pagearray) || $page == "logout.php") {
						header("Location: login.php");
						exit;
					}
				} else {
					if(in_array($page, $pagearray)) {
					    if ($this->uid == 1) {
					        header("Location: nachrichten.php");
					        exit;
					    } else {
					        header("Location: dorf1.php");
					        exit;
					    }
					}

				}
			}
};
$session = new Session;
$form = new Form;

// if there is no user, we'd try to load messages for user with ID 0, which is wrong
if (!empty($_SESSION['id_user'])) {
    $message = new Message;

    // create a global user variable which will later be removed from here
    // and created + retrieved either via Service Locator or other DI concept
    $user = new User((int) $_SESSION['id_user'], $database);
}

?>

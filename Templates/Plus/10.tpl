<?php
//////////////     made by alq0rsan, improved by evader   /////////////////////////
if($session->gold >= 5){
    $rows = $database->query_return("SELECT gold, b2 FROM ".TB_PREFIX."users WHERE id = ".(int)$session->uid." LIMIT 1");
    $golds = isset($rows[0]) ? $rows[0] : null; 
	if($session->sit == 0) {
		if ($golds) {
			if((int)$golds['gold'] >= 5) {
				if((int)$golds['b2'] < time()) {
					$database->query("UPDATE ".TB_PREFIX."users SET b2 = ".(time()+PLUS_PRODUCTION)." WHERE id = ".(int)$session->uid);
				} else {
					$database->query("UPDATE ".TB_PREFIX."users SET b2 = ".((int)$golds['b2']+PLUS_PRODUCTION)." WHERE id = ".(int)$session->uid);
				}
				$done1 = "+25% Production: Clay";
				$database->query("UPDATE ".TB_PREFIX."users SET gold = ".((int)$session->gold-5)." WHERE id = ".(int)$session->uid);
				$database->query("INSERT INTO ".TB_PREFIX."gold_fin_log (wid,log) VALUES (".(int)$village->wid.", '+25%  Production: Clay')");
			} else {
				$done1 = "You need more gold";
			}
		} else {
			$done1 = "Failed clay attempt";
			$database->query("INSERT INTO ".TB_PREFIX."gold_fin_log (wid,log) VALUES (".(int)$village->wid.", 'Failed +25%  Production: Clay')");
		}
	}
	header("Location: plus.php?id=3");
	exit;
}
 ?>

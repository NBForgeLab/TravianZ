<?php
//////////////     made by alq0rsan, improved by evader   /////////////////////////
if($session->gold >= 10){
    $rows = $database->query_return("SELECT gold, plus FROM ".TB_PREFIX."users WHERE id = ".(int)$session->uid." LIMIT 1");
    $golds = isset($rows[0]) ? $rows[0] : null;
	if($session->sit == 0) {
		if ($golds) {
			if((int)$golds['gold'] >= 10) {
				if((int)$golds['plus'] == 0) {
					$database->query("UPDATE ".TB_PREFIX."users SET plus = ".(mktime(date("H"),date("i"), date("s"),date("m") , date("d"), date("Y"))+PLUS_TIME)." WHERE id = ".(int)$session->uid);
				} else {
					$database->query("UPDATE ".TB_PREFIX."users SET plus = ".((int)$golds['plus']+PLUS_TIME)." WHERE id = ".(int)$session->uid);
				}
				$done1 = "&nbsp;&nbsp;Plus Account";
				$database->query("UPDATE ".TB_PREFIX."users SET gold = ".((int)$session->gold-10)." WHERE id = ".(int)$session->uid);
				$database->query("INSERT INTO ".TB_PREFIX."gold_fin_log (wid,log) VALUES (".(int)$village->wid.", 'Plus Account')");
			} else {
				$done1 = "&nbsp;&nbsp;You need more gold";
			}
		} else {
			$done1 = "Failed plus attempt";
			$database->query("INSERT INTO ".TB_PREFIX."gold_fin_log (wid,log) VALUES (".(int)$village->wid.", 'Failed Plus Account')");
		}
	}
	header("Location: plus.php?id=3");
	exit;
}
 ?>

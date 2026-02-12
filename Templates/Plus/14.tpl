<?php
//////////////     made by alq0rsan   /////////////////////////

    $rowsV = $database->query_return("SELECT wood, clay, iron, crop FROM ".TB_PREFIX."vdata WHERE wref = ".(int)$village->wid." LIMIT 1");
    $uuVilid = isset($rowsV[0]) ? $rowsV[0] : ['wood'=>0,'clay'=>0,'iron'=>0,'crop'=>0];

    $totalT = ($T1+$T2+$T3+$T4);
    $totalR = ((int)$uuVilid['wood']+(int)$uuVilid['clay']+(int)$uuVilid['iron']+(int)$uuVilid['crop']);

    $rowsLog = $database->query_return("SELECT Count(*) as Total FROM ".TB_PREFIX."gold_fin_log");
    $goldlog = isset($rowsLog[0]) ? $rowsLog[0] : ['Total'=>0];

if($totalT <= $totalR) {
$database->query("UPDATE ".TB_PREFIX."vdata SET wood = ".(int)$T1.", clay = ".(int)$T2.", iron = ".(int)$T3.", crop = ".(int)$T4." WHERE wref = ".(int)$village->wid);
    $database->query("UPDATE ".TB_PREFIX."users SET gold = ".((int)$session->gold-3)." WHERE id = ".(int)$session->uid);
    $database->query("INSERT INTO ".TB_PREFIX."gold_fin_log (id,wid,log) VALUES (".((int)$goldlog['Total']+1).", ".(int)$village->wid.", 'trade 1:1')");
echo "done";
} else {
echo "failed";
    $database->query("INSERT INTO ".TB_PREFIX."gold_fin_log (id,wid,log) VALUES (".((int)$goldlog['Total']+1).", ".(int)$village->wid.", 'Failed trade 1:1')");

}

header("Location: plus.php?id=3");
exit;

 ?>

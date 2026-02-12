<?php
//////////////     made by alq0rsan   /////////////////////////
if($session->gold >= 100 && $session->sit == 0 && $session->goldclub == 0) {
    $database->query("UPDATE ".TB_PREFIX."users SET goldclub = 1, gold = gold - 100 WHERE id = ".(int)$session->uid);
}
header("Location: plus.php?id=3");
exit;
?>

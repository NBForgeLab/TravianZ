<div id="content"  class="plus">
<h1>Travian <font color="#71D000">P</font><font color="#FF6F0F">l</font><font  color="#71D000">u</font><font color="#FF6F0F">s</font></h1>
<div id="textmenu">
   <a href="plus.php" <?php

        $id = $_GET['id'] ?? null;

        if($id === null && @(basename($_SERVER['REQUEST_URI']) !== 'a2b2.php')) {
        	echo "class=\"selected\"";
        }
        if($id !== null && (((int) $id) === 1 || strlen((string) $id) === 3)) {
        	echo "class=\"selected\"";
        }

?>>Tariffs</a>

 | <a href="plus.php?id=2" <?php

        if($id !== null && ((int) $id) === 2) {
        	echo "class=\"selected\"";
        }
        if($id !== null && ((int) $id) >= 6 && strlen((string) $id) < 3) {
        	echo "class=\"selected\"";
        }

?>>Advantages</a>

 | <a href="plus.php?id=3" <?php

        if($id !== null && ((int) $id) === 3) {
        	echo "class=\"selected\"";
        }
        if($id !== null && ((int) $id) >= 6 && strlen((string) $id) < 3) {
        	echo "class=\"selected\"";
        }

?>>Gold</a>

 | <a href="plus.php?id=4" <?php

        if($id !== null && ((int) $id) === 4) {
        	echo "class=\"selected\"";
        }

?>>FAQ</a>

 | <a href="plus.php?id=5" <?php

        if($id !== null && ((int) $id) === 5) {
        	echo "class=\"selected\"";
        }
        if($id !== null && ((int) $id) >= 6 && strlen((string) $id) < 3) {
        	echo "class=\"selected\"";
        }

?>>Earn gold</a>
| <a href="a2b2.php" <?php

        if(@(basename($_SERVER['REQUEST_URI']) === 'a2b2.php')) {
            echo "class=\"selected\"";
        }
?>>Account Statement</a>

</div>

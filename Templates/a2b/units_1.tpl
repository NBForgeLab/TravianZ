<h1>Send Troops</h1>

<form method="POST" name="snd" action="a2b.php"><input name="timestamp" value="1278280730" type="hidden"> <input name="timestamp_checksum" value="597fa8" type="hidden"> <input name="b" value="1" type="hidden">


		<table id="troops" cellpadding="1" cellspacing="1">
	<tbody><tr>
		<td class="line-first column-first large"><img class="unit u1" src="img/x.gif" title="Legionnaire" alt="Legionnaire" data-clear-target="t1"> <input class="text" <?php if ($village->unitarray['u1']<=0) {echo ' disabled="disabled"';}?> name="t1" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u1']>0){
        	echo "<a href=\"#\" data-fill-target=\"t1\" data-fill-value=\"".$village->unitarray['u1']."\">(".$village->unitarray['u1'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		
        <td class="line-first large"><img class="unit u4" src="img/x.gif" title="Equites Legati" alt="Equites Legati"> <input class="text" <?php if ($village->unitarray['u4']<=0) {echo ' disabled="disabled"';}?> name="t4" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u4']>0){
        	echo "<a href=\"#\" data-fill-target=\"t4\" data-fill-value=\"".$village->unitarray['u4']."\">(".$village->unitarray['u4'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
        <td class="line-first regular"><img class="unit u7" src="img/x.gif" title="Battering Ram" alt="Battering Ram"> <input class="text" <?php if ($village->unitarray['u7']<=0) {echo ' disabled="disabled"';}?> name="t7" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u7']>0){
        	echo "<a href=\"#\" data-fill-target=\"t7\" data-fill-value=\"".$village->unitarray['u7']."\">(".$village->unitarray['u7'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		
        <td class="line-first column-last small"><img class="unit u9" src="img/x.gif" title="Senator" alt="Senator"> <input class="text" <?php if ($village->unitarray['u9']<=0) {echo ' disabled="disabled"';}?> name="t9" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u9']>0){
        	echo "<a href=\"#\" data-fill-target=\"t9\" data-fill-value=\"".$village->unitarray['u9']."\">(".$village->unitarray['u9'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="column-first large"><img class="unit u2" src="img/x.gif" title="Praetorian" alt="Praetorian"> <input class="text" <?php if ($village->unitarray['u2']<=0) {echo ' disabled="disabled"';}?> name="t2" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u2']>0){
        	echo "<a href=\"#\" data-fill-target=\"t2\" data-fill-value=\"".$village->unitarray['u2']."\">(".$village->unitarray['u2'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		<td class="large"><img class="unit u5" src="img/x.gif" title="Equites Imperatoris" alt="Equites Imperatoris"> <input class="text" <?php if ($village->unitarray['u5']<=0) {echo ' disabled="disabled"';}?> name="t5" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u5']>0){
        	echo "<a href=\"#\" data-fill-target=\"t5\" data-fill-value=\"".$village->unitarray['u5']."\">(".$village->unitarray['u5'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="regular"><img class="unit u8" src="img/x.gif" title="Fire Catapult" alt="Fire Catapult"> <input class="text" <?php if ($village->unitarray['u8']<=0) {echo ' disabled="disabled"';}?> name="t8" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u8']>0){
        	echo "<a href=\"#\" data-fill-target=\"t8\" data-fill-value=\"".$village->unitarray['u8']."\">(".$village->unitarray['u8'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="column-last small"><img class="unit u10" src="img/x.gif" title="Settler" alt="Settler"> <input class="text" <?php if ($village->unitarray['u10']<=0) {echo ' disabled="disabled"';}?> name="t10" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u10']>0){
        	echo "<a href=\"#\" data-fill-target=\"t10\" data-fill-value=\"".$village->unitarray['u10']."\">(".$village->unitarray['u10'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="line-last column-first large"><img class="unit u3" src="img/x.gif" title="Imperian" alt="Imperian"> <input class="text" <?php if ($village->unitarray['u3']<=0) {echo ' disabled="disabled"';}?> name="t3" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u3']>0){
        	echo "<a href=\"#\" data-fill-target=\"t3\" data-fill-value=\"".$village->unitarray['u3']."\">(".$village->unitarray['u3'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="line-last large"><img class="unit u6" src="img/x.gif" title="Equites Caesaris" alt="Equites Caesaris"> <input class="text" <?php if ($village->unitarray['u6']<=0) {echo ' disabled="disabled"';}?> name="t6" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u6']>0){
        	echo "<a href=\"#\" data-fill-target=\"t6\" data-fill-value=\"".$village->unitarray['u6']."\">(".$village->unitarray['u6'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="line-last regular"><?php 
        if ($village->unitarray['hero']>0){
        echo "<img class=\"unit uhero\" src=\"img/x.gif\" title=\"Hero\" alt=\"Hero\"> <input class=\"text\" name=\"t11\" value=\"\" maxlength=\"6\" type=\"text\">   ";
            echo "<a href=\"#\" data-fill-target=\"t11\" data-fill-value=\"".$village->unitarray['hero']."\">(".$village->unitarray['hero'].")</a></td>";
        }
        ?></td>
			<td class="line-last column-last"></td>		</tr>
</tbody></table>


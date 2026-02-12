<h1>Send Troops</h1>

<form method="POST" name="snd" action="a2b.php"><input name="timestamp" value="1278280730" type="hidden"> <input name="timestamp_checksum" value="597fa8" type="hidden"> <input name="b" value="1" type="hidden">


		<table id="troops" cellpadding="1" cellspacing="1">
	<tbody><tr>
		<td class="line-first column-first large"><img class="unit u11" src="img/x.gif" title="Clubswinger" alt="Clubswinger" data-clear-target="t1"> <input class="text" <?php if ($village->unitarray['u11']<=0) {echo ' disabled="disabled"';}?> name="t1" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u11']>0){
        	echo "<a href=\"#\" data-fill-target=\"t1\" data-fill-value=\"".$village->unitarray['u11']."\">(".$village->unitarray['u11'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		
        <td class="line-first large"><img class="unit u14" src="img/x.gif" title="Scout" alt="Scout"> <input class="text" <?php if ($village->unitarray['u14']<=0) {echo ' disabled="disabled"';}?> name="t4" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u14']>0){
        	echo "<a href=\"#\" data-fill-target=\"t4\" data-fill-value=\"".$village->unitarray['u14']."\">(".$village->unitarray['u14'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
        <td class="line-first regular"><img class="unit u17" src="img/x.gif" title="Ram" alt="Ram"> <input class="text" <?php if ($village->unitarray['u17']<=0) {echo ' disabled="disabled"';}?> name="t7" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u17']>0){
        	echo "<a href=\"#\" data-fill-target=\"t7\" data-fill-value=\"".$village->unitarray['u17']."\">(".$village->unitarray['u17'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		
        <td class="line-first column-last small"><img class="unit u19" src="img/x.gif" title="Chief" alt="Chief"> <input class="text" <?php if ($village->unitarray['u19']<=0) {echo ' disabled="disabled"';}?> name="t9" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u19']>0){
        	echo "<a href=\"#\" data-fill-target=\"t9\" data-fill-value=\"".$village->unitarray['u19']."\">(".$village->unitarray['u19'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="column-first large"><img class="unit u12" src="img/x.gif" title="Spearman" alt="Spearman"> <input class="text" <?php if ($village->unitarray['u12']<=0) {echo ' disabled="disabled"';}?> name="t2" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u12']>0){
        	echo "<a href=\"#\" data-fill-target=\"t2\" data-fill-value=\"".$village->unitarray['u12']."\">(".$village->unitarray['u12'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		<td class="large"><img class="unit u15" src="img/x.gif" title="Paladin" alt="Paladin"> <input class="text" <?php if ($village->unitarray['u15']<=0) {echo ' disabled="disabled"';}?> name="t5" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u15']>0){
        	echo "<a href=\"#\" data-fill-target=\"t5\" data-fill-value=\"".$village->unitarray['u15']."\">(".$village->unitarray['u15'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="regular"><img class="unit u18" src="img/x.gif" title="Catapult" alt="Catapult"> <input class="text" <?php if ($village->unitarray['u18']<=0) {echo ' disabled="disabled"';}?> name="t8" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u18']>0){
        	echo "<a href=\"#\" data-fill-target=\"t8\" data-fill-value=\"".$village->unitarray['u18']."\">(".$village->unitarray['u18'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="column-last small"><img class="unit u20" src="img/x.gif" title="Settler" alt="Settler"> <input class="text" <?php if ($village->unitarray['u20']<=0) {echo ' disabled="disabled"';}?> name="t10" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u20']>0){
        	echo "<a href=\"#\" data-fill-target=\"t10\" data-fill-value=\"".$village->unitarray['u20']."\">(".$village->unitarray['u20'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="line-last column-first large"><img class="unit u13" src="img/x.gif" title="Axeman" alt="Axeman"> <input class="text" <?php if ($village->unitarray['u13']<=0) {echo ' disabled="disabled"';}?> name="t3" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u13']>0){
        	echo "<a href=\"#\" data-fill-target=\"t3\" data-fill-value=\"".$village->unitarray['u13']."\">(".$village->unitarray['u13'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="line-last large"><img class="unit u16" src="img/x.gif" title="Teutonic Knight" alt="Teutonic Knight"> <input class="text" <?php if ($village->unitarray['u16']<=0) {echo ' disabled="disabled"';}?> name="t6" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u16']>0){
        	echo "<a href=\"#\" data-fill-target=\"t6\" data-fill-value=\"".$village->unitarray['u16']."\">(".$village->unitarray['u16'].")</a></td>";
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


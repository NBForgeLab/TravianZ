<h1>Send Troops</h1>

<form method="POST" name="snd" action="a2b.php"><input name="timestamp" value="3278280730" type="hidden"> <input name="timestamp_checksum" value="597fa8" type="hidden"> <input name="b" value="1" type="hidden">


		<table id="troops" cellpadding="1" cellspacing="1">
	<tbody><tr>
		<td class="line-first column-first large"><img class="unit u31" src="img/x.gif" title="<?php echo U31; ?>" alt="<?php echo U31; ?>" data-clear-target="t1"> <input class="text" <?php if ($village->unitarray['u31']<=0) {echo ' disabled="disabled"';}?> name="t1" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u31']>0){
        	echo "<a href=\"#\" data-fill-target=\"t1\" data-fill-value=\"".$village->unitarray['u31']."\">(".$village->unitarray['u31'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		
        <td class="line-first large"><img class="unit u34" src="img/x.gif" title="<?php echo U34; ?>" alt="<?php echo U34; ?>"> <input class="text" <?php if ($village->unitarray['u34']<=0) {echo ' disabled="disabled"';}?> name="t4" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u34']>0){
        	echo "<a href=\"#\" data-fill-target=\"t4\" data-fill-value=\"".$village->unitarray['u34']."\">(".$village->unitarray['u34'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
        <td class="line-first regular"><img class="unit u37" src="img/x.gif" title="<?php echo U37; ?>" alt="<?php echo U37; ?>"> <input class="text" <?php if ($village->unitarray['u37']<=0) {echo ' disabled="disabled"';}?> name="t7" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u37']>0){
        	echo "<a href=\"#\" data-fill-target=\"t7\" data-fill-value=\"".$village->unitarray['u37']."\">(".$village->unitarray['u37'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		
        <td class="line-first column-last small"><img class="unit u39" src="img/x.gif" title="<?php echo U39; ?>" alt="<?php echo U39; ?>"> <input class="text" <?php if ($village->unitarray['u39']<=0) {echo ' disabled="disabled"';}?> name="t9" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u39']>0){
        	echo "<a href=\"#\" data-fill-target=\"t9\" data-fill-value=\"".$village->unitarray['u39']."\">(".$village->unitarray['u39'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="column-first large"><img class="unit u32" src="img/x.gif" title="<?php echo U32; ?>" alt="<?php echo U32; ?>"> <input class="text" <?php if ($village->unitarray['u32']<=0) {echo ' disabled="disabled"';}?> name="t2" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u32']>0){
        	echo "<a href=\"#\" data-fill-target=\"t2\" data-fill-value=\"".$village->unitarray['u32']."\">(".$village->unitarray['u32'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		<td class="large"><img class="unit u35" src="img/x.gif" title="<?php echo U35; ?>" alt="<?php echo U35; ?>"> <input class="text" <?php if ($village->unitarray['u35']<=0) {echo ' disabled="disabled"';}?> name="t5" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u35']>0){
        	echo "<a href=\"#\" data-fill-target=\"t5\" data-fill-value=\"".$village->unitarray['u35']."\">(".$village->unitarray['u35'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="regular"><img class="unit u38" src="img/x.gif" title="<?php echo U38; ?>" alt="<?php echo U38; ?>"> <input class="text" <?php if ($village->unitarray['u38']<=0) {echo ' disabled="disabled"';}?> name="t8" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u38']>0){
        	echo "<a href=\"#\" data-fill-target=\"t8\" data-fill-value=\"".$village->unitarray['u38']."\">(".$village->unitarray['u38'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="column-last small"><img class="unit u40" src="img/x.gif" title="<?php echo U40; ?>" alt="<?php echo U40; ?>"> <input class="text" <?php if ($village->unitarray['u40']<=0) {echo ' disabled="disabled"';}?> name="t10" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u40']>0){
        	echo "<a href=\"#\" data-fill-target=\"t10\" data-fill-value=\"".$village->unitarray['u40']."\">(".$village->unitarray['u40'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="line-last column-first large"><img class="unit u33" src="img/x.gif" title="<?php echo U33; ?>" alt="<?php echo U33; ?>"> <input class="text" <?php if ($village->unitarray['u33']<=0) {echo ' disabled="disabled"';}?> name="t3" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u33']>0){
        	echo "<a href=\"#\" data-fill-target=\"t3\" data-fill-value=\"".$village->unitarray['u33']."\">(".$village->unitarray['u33'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="line-last large"><img class="unit u36" src="img/x.gif" title="<?php echo U36; ?>" alt="<?php echo U36; ?>"> <input class="text" <?php if ($village->unitarray['u36']<=0) {echo ' disabled="disabled"';}?> name="t6" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u36']>0){
        	echo "<a href=\"#\" data-fill-target=\"t6\" data-fill-value=\"".$village->unitarray['u36']."\">(".$village->unitarray['u36'].")</a></td>";
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


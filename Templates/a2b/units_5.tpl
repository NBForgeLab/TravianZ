<h1>Send Troops</h1>

<form method="POST" name="snd" action="a2b.php"><input name="timestamp" value="3278280730" type="hidden"> <input name="timestamp_checksum" value="597fa8" type="hidden"> <input name="b" value="1" type="hidden">


		<table id="troops" cellpadding="1" cellspacing="1">
	<tbody><tr>
		<td class="line-first column-first large"><img class="unit u41" src="img/x.gif" title="<?php echo U41; ?>" alt="<?php echo U41; ?>" data-clear-target="t1"> <input class="text" <?php if ($village->unitarray['u41']<=0) {echo ' disabled="disabled"';}?> name="t1" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u41']>0){
        	echo "<a href=\"#\" data-fill-target=\"t1\" data-fill-value=\"".$village->unitarray['u41']."\">(".$village->unitarray['u41'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		
        <td class="line-first large"><img class="unit u44" src="img/x.gif" title="<?php echo U44; ?>" alt="<?php echo U44; ?>"> <input class="text" <?php if ($village->unitarray['u44']<=0) {echo ' disabled="disabled"';}?> name="t4" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u44']>0){
        	echo "<a href=\"#\" data-fill-target=\"t4\" data-fill-value=\"".$village->unitarray['u44']."\">(".$village->unitarray['u44'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
        <td class="line-first regular"><img class="unit u47" src="img/x.gif" title="<?php echo U47; ?>" alt="<?php echo U47; ?>"> <input class="text" <?php if ($village->unitarray['u47']<=0) {echo ' disabled="disabled"';}?> name="t7" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u47']>0){
        	echo "<a href=\"#\" data-fill-target=\"t7\" data-fill-value=\"".$village->unitarray['u47']."\">(".$village->unitarray['u47'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		
        <td class="line-first column-last small"><img class="unit u49" src="img/x.gif" title="<?php echo U49; ?>" alt="<?php echo U49; ?>"> <input class="text" <?php if ($village->unitarray['u49']<=0) {echo ' disabled="disabled"';}?> name="t9" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u49']>0){
        	echo "<a href=\"#\" data-fill-target=\"t9\" data-fill-value=\"".$village->unitarray['u49']."\">(".$village->unitarray['u49'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="column-first large"><img class="unit u42" src="img/x.gif" title="<?php echo U42; ?>" alt="<?php echo U42; ?>"> <input class="text" <?php if ($village->unitarray['u42']<=0) {echo ' disabled="disabled"';}?> name="t2" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u42']>0){
        	echo "<a href=\"#\" data-fill-target=\"t2\" data-fill-value=\"".$village->unitarray['u42']."\">(".$village->unitarray['u42'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		<td class="large"><img class="unit u45" src="img/x.gif" title="<?php echo U45; ?>" alt="<?php echo U45; ?>"> <input class="text" <?php if ($village->unitarray['u45']<=0) {echo ' disabled="disabled"';}?> name="t5" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u45']>0){
        	echo "<a href=\"#\" data-fill-target=\"t5\" data-fill-value=\"".$village->unitarray['u45']."\">(".$village->unitarray['u45'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="regular"><img class="unit u48" src="img/x.gif" title="<?php echo U48; ?>" alt="<?php echo U48; ?>"> <input class="text" <?php if ($village->unitarray['u48']<=0) {echo ' disabled="disabled"';}?> name="t8" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u48']>0){
        	echo "<a href=\"#\" data-fill-target=\"t8\" data-fill-value=\"".$village->unitarray['u48']."\">(".$village->unitarray['u48'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="column-last small"><img class="unit u50" src="img/x.gif" title="<?php echo U50; ?>" alt="<?php echo U50; ?>"> <input class="text" <?php if ($village->unitarray['u50']<=0) {echo ' disabled="disabled"';}?> name="t10" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u50']>0){
        	echo "<a href=\"#\" data-fill-target=\"t10\" data-fill-value=\"".$village->unitarray['u50']."\">(".$village->unitarray['u50'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="line-last column-first large"><img class="unit u43" src="img/x.gif" title="<?php echo U43; ?>" alt="<?php echo U43; ?>"> <input class="text" <?php if ($village->unitarray['u43']<=0) {echo ' disabled="disabled"';}?> name="t3" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u43']>0){
        	echo "<a href=\"#\" data-fill-target=\"t3\" data-fill-value=\"".$village->unitarray['u43']."\">(".$village->unitarray['u43'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="line-last large"><img class="unit u46" src="img/x.gif" title="<?php echo U46; ?>" alt="<?php echo U46; ?>"> <input class="text" <?php if ($village->unitarray['u46']<=0) {echo ' disabled="disabled"';}?> name="t6" value="" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u46']>0){
        	echo "<a href=\"#\" data-fill-target=\"t6\" data-fill-value=\"".$village->unitarray['u46']."\">(".$village->unitarray['u46'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="line-last regular"></td>
			<td class="line-last column-last"></td>		</tr>
</tbody></table>


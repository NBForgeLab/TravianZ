<?php
$tribe1 = $database->query_return("SELECT id FROM ".TB_PREFIX."users WHERE tribe = 1");
$tribe2 = $database->query_return("SELECT id FROM ".TB_PREFIX."users WHERE tribe = 2");
$tribe3 = $database->query_return("SELECT id FROM ".TB_PREFIX."users WHERE tribe = 3");
$tribes = [is_array($tribe1)?count($tribe1):0, is_array($tribe2)?count($tribe2):0, is_array($tribe3)?count($tribe3):0];
$usersRows = $database->query_return("SELECT id FROM ".TB_PREFIX."users WHERE tribe > 0 AND tribe < 4");
$users = is_array($usersRows) ? count($usersRows) : 0;
?>
<br /><br /><br /><br /><br />
	<table id="profile">
		<thead>
			<tr>
				<th colspan="2">World Information</th>
			</tr>
		 </thead>
		 <tbody>
			<tr>
				<td>Registered players</td>
				<td><?php echo $users; ?></td>
			</tr>
			<tr>
				<td>Active players</td>
				<td><?php $rows = $database->query_return("SELECT id FROM ".TB_PREFIX."active"); echo (is_array($rows)?count($rows):0); ?></td>
			</tr>
			<tr>
				<td>Players online</td>
				<td><?php $t =time();
				$rows = $database->query_return("SELECT id FROM ".TB_PREFIX."users WHERE timestamp > ".(int)($t - 300));
				echo (is_array($rows)?count($rows):0);?>
				</td>
			</tr>
			<tr>
				<td>Players Banned</td>
				<td><?php
				$rows = $database->query_return("SELECT id FROM ".TB_PREFIX."users WHERE access = 0");
				echo (is_array($rows)?count($rows):0); ?>
				</td>
			</tr>
			<tr>
				<td>Villages settled</td>
				<td><?php
				$rows = $database->query_return("SELECT Count(*) as Total FROM ".TB_PREFIX."vdata");
				$num_rows = isset($rows[0]['Total']) ? (int)$rows[0]['Total'] : 0;
				echo $num_rows;
            ?>
				</td>
			</tr>
			<tr>
				<td>Total Population</td>
			<td><?php $rows = $database->query_return("SELECT SUM(pop) AS sumofpop FROM ".TB_PREFIX."vdata"); echo (int)($rows[0]['sumofpop'] ?? 0); ?></td>
			</tr>
		</tbody>
	</table>

	<br />

	<table id="profile">
		<thead>
			<tr><th colspan="3">Player Information</th></tr>
			<td class="b">Tribe</td>
			<td class="b">Registered</td>
			<td class="b">Percent</td>
		</thead>
		<tbody>
			<tr>
				<td>Romans</td>
				<td><?php echo $tribes[0]; ?></td>
				<td><?php echo ($users > 0) ? ($percents[0] = round(100 * ($tribes[0] / $users), 2))."%" : "---"; ?></td>
			</tr>
			<tr>
				<td>Teutons</td>
				<td><?php echo $tribes[1]; ?></td>
				<td><?php echo ($users > 0) ? ($percents[1] = round(100 * ($tribes[1] / $users), 2))."%" : "---"; ?></td>
			</tr>
			<tr>
				<td>Gauls</td>
				<td><?php echo $tribes[2]; ?></td>
				<td><?php echo ($users > 0) ? (100-$percents[0]-$percents[1])."%" : "---"; ?></td>
			</tr>
		</tbody>
	</table>

	<br />

	<table id="profile">
		<thead>
		 <tr>
			<th colspan="3">Server Information</th>
		</tr>
			<td class="b"></td>
			<td class="b">Total</td>
			<td class="b">Average</td>
		</thead>
		<tbody>
			<tr>
				<td><img src="../<?php echo GP_LOCATE; ?>img/a/gold.gif" alt="Gold" title="Gold"> Gold</td>
				<td><?php $rows = $database->query_return("SELECT SUM(gold) AS sumofgold FROM ".TB_PREFIX."users"); $sum = (int)($rows[0]['sumofgold'] ?? 0); echo $sum; ?></td>
				<td><?php $rows = $database->query_return("SELECT SUM(gold) AS sumofgold FROM ".TB_PREFIX."users"); $sum = (int)($rows[0]['sumofgold'] ?? 0); echo ($users>0?round($sum / $users):0);?></td>
			</tr>
		</tbody>
	</table>
</div>
	<table id="member">
		<thead>
			<tr>
				<th colspan="10">Troops on the Server</th>
			</tr>
			<?php
				$cells = ['SUM(hero) as hero'];

				for($i=1; $i<51; $i++) {
					array_push($cells, 'SUM(u'.$i.') AS u'.$i);
				}

				$units_villages = $database->query_return("SELECT ".implode(',', $cells)." FROM ".TB_PREFIX."units");
				$units_villages = (is_array($units_villages) && count($units_villages)) ? $units_villages[0] : [];
				$units_enforcements = $database->query_return("SELECT ".implode(',', $cells)." FROM ".TB_PREFIX."enforcement");
				$units_enforcements = (is_array($units_enforcements) && count($units_enforcements)) ? $units_enforcements[0] : [];

				for($i=1; $i<11; $i++) {
					echo '<td class="on"><img src="../'.GP_LOCATE.'img/u/'.$i.'.gif"></td>';
				}

				echo '</thead><tbody>';
				for($i=1; $i<11; $i++) {
					echo '<td class="on">'.($units_villages['u'.$i] + $units_enforcements['u'.$i]).'</td>';
				}

				echo "</tr>";
				for($i=11; $i<21; $i++) {
					echo '<td class="on"><img src="../'.GP_LOCATE.'img/u/'.$i.'.gif"></td>';
				}

				echo '</thead><tbody>';
				for($i=11; $i<21; $i++) {
					echo '<td class="on">'.($units_villages['u'.$i] + $units_enforcements['u'.$i]).'</td>';
				}

				echo "</tr>";
				for($i=21; $i<31; $i++) {
					echo '<td class="on"><img src="../'.GP_LOCATE.'img/u/'.$i.'.gif"></td>';
				}

				echo '</thead><tbody>';
				for($i=21; $i<31; $i++) {
					echo '<td class="on">'.($units_villages['u'.$i] + $units_enforcements['u'.$i]).'</td>';
				}

				echo "</tr>";
				for($i=31; $i<41; $i++) {
					echo '<td class="on"><img src="../'.GP_LOCATE.'img/u/'.$i.'.gif"></td>';
				}

				echo '</thead><tbody>';
				for($i=31; $i<41; $i++) {
					echo '<td class="on">'.($units_villages['u'.$i] + $units_enforcements['u'.$i]).'</td>';
				}

				echo "</tr>";
				for($i=41; $i<51; $i++) {
					echo '<td class="on"><img src="../'.GP_LOCATE.'img/u/'.$i.'.gif"></td>';
				}

				echo '</thead><tbody>';
				for($i=41; $i<51; $i++) {
					echo '<td class="on">'.($units_villages['u'.$i] + $units_enforcements['u'.$i]).'</td>';
				}
			?>
		</tbody>
	</table>
<div>

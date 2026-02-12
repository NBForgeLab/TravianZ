<?php
$id = $_GET['did'];
if(isset($id))
{
	?>
	<br /><br />
	<table id="profile">
		<thead>
		<tr>
				<th colspan="3" class="on"><a href="#"><?php echo $village['name']; ?></a>'s Build Log</th>
			</tr>
			<tr>
				<td style="width: 12%">#</td>
				<td>Event</td>
				<td>Date</td>
			</tr>
		</thead>
			<?php
				$sql = "SELECT * FROM ".TB_PREFIX."build_log WHERE wid = ".(int) $_GET['did'];
				$result = $database->query_return($sql);
				$j = 0;
				foreach ($result as $row)
				{
					echo '
					<tr>
						<td>'.++$j.'</td>
						<td>'.$row['log'].'</td>
						<td style="white-space: nowrap">'.$row['date'].'</td>
					</tr>';
				}
			?>
		</thead>
	</table><?php
}
else
{
	include("404.tpl");
}
?>

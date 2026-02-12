<?php

if($_SESSION['access'] < ADMIN) die("Access Denied: You are not Admin!");
$id = (int) $_SESSION['id'];
if(isset($_GET['uid']))
{
	$rows = $database->query_return("SELECT access FROM ".TB_PREFIX."users WHERE id = ".(int) $_GET['uid']." LIMIT 1");
	$curaccess = isset($rows[0]['access']) ? (int)$rows[0]['access'] : 2;
	$pRows = $database->query_return("SELECT * FROM ".TB_PREFIX."users WHERE id = ".(int)$id." LIMIT 1");
	$player = isset($pRows[0]) ? $pRows[0] : [];
	?>

	<form action="../GameEngine/Admin/Mods/editAccess.php" method="POST">
		<input type="hidden" name="admid" id="admid" value="<?php echo $_SESSION['id']; ?>">
		<input type="hidden" name="uid" id="uid" value="<?php echo $_GET['uid']; ?>">
		<table id="member" style="width:300px;">
			<thead>
				<tr>
					<th colspan="2">Edit <?php echo $player['username']; ?>'s access</th>
				</tr>
				<tr>
					<td></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<center>
							<b>Change Access</b>
						</center>
					</td>
					<td>
						<center>
							<select name="access" class="dropdown">
								<option value="0" <?php if($curaccess == 0) { echo 'selected="selected"'; } else { echo ''; } ?>>Banned</option>
								<option value="2" <?php if($curaccess == 2) { echo 'selected="selected"'; } else { echo ''; } ?>>Normal User</option>
								<option value="8" <?php if($curaccess == 8) { echo 'selected="selected"'; } else { echo ''; } ?>>Multihunter</option>
								<option value="9" <?php if($curaccess == 9) { echo 'selected="selected"'; } else { echo ''; } ?>>Admin</option>
							</select>
						</center>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<center>
							<input type="image" src="../img/admin/b/ok1.gif" value="submit" title="Give Players Free Gold">
						</center>
					</td>
				</tr>
			</tbody>
		</table>
	</form><?php
	if(isset($_GET['g']))
	{
		echo '<br /><br /><font color="Red"><b>Players Access Changed</font></b>';
	}
}
else
{
	include("404.tpl");
}
?>

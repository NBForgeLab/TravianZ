<?php


if(isset($_GET['err']) && $_GET['err'] == 1) {
	echo '<div class="alert alert-warning">At least Multihunter &amp; Support password are required in this form.</div>';
}

if(isset($_GET['err']) && $_GET['err'] == 2) {
	echo '<div class="alert alert-warning">Natars is a reserved username for an in-game NPC tribe. Please choose a different admin username.</div>';
}

?>

<form action="include/accounts.php?t=<?php echo isset($_GET['t']) ? (int) $_GET['t'] : 1; ?><?php echo (isset($_GET['rtl']) && $_GET['rtl'] === '1') ? '&rtl=1' : ''; ?>" method="post" id="dataform" class="vstack gap-4" onsubmit="return proceed();">
	<div class="card">
		<div class="card-header">Multihunter account</div>
		<div class="card-body">
			<div class="row g-3 align-items-center">
				<div class="col-sm-4">
					<label class="col-form-label" for="mhuser">Name</label>
				</div>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="mhuser" id="mhuser" value="Multihunter" disabled="disabled">
				</div>
				<div class="col-sm-4">
					<label class="col-form-label" for="mhpw">Password</label>
				</div>
				<div class="col-sm-8">
					<input type="password" class="form-control" name="mhpw" id="mhpw" value="">
					<div class="form-text">Remember this password. You need it for the Admin.</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-header">Support account</div>
		<div class="card-body">
			<div class="row g-3 align-items-center">
				<div class="col-sm-4">
					<label class="col-form-label" for="suser">Name</label>
				</div>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="suser" id="suser" value="Support" disabled="disabled">
				</div>
				<div class="col-sm-4">
					<label class="col-form-label" for="spw">Password</label>
				</div>
				<div class="col-sm-8">
					<input type="password" class="form-control" name="spw" id="spw" value="">
					<div class="form-text">Remember this password. You need it for the Admin.</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-header">Admin account (optional)</div>
		<div class="card-body">
			<div class="row g-3 align-items-center">
				<div class="col-sm-4">
					<label class="col-form-label" for="aname">Admin name</label>
				</div>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="aname" id="aname" value="">
				</div>

				<div class="col-sm-4">
					<label class="col-form-label" for="aemail">Admin email</label>
				</div>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="aemail" id="aemail" value="">
				</div>

				<div class="col-sm-4">
					<label class="col-form-label" for="apass">Admin password</label>
				</div>
				<div class="col-sm-8">
					<input type="password" class="form-control" name="apass" id="apass" value="">
				</div>

				<div class="col-sm-4">
					<label class="col-form-label" for="atribe">Admin tribe</label>
				</div>
				<div class="col-sm-8">
					<select class="form-select" name="atribe" id="atribe">
						<option value="1" selected="selected">Romans</option>
						<option value="2">Teutons</option>
						<option value="3">Gauls</option>
					</select>
				</div>

				<div class="col-sm-4">
					<label class="col-form-label" for="admin_rank">Show admin in stats</label>
				</div>
				<div class="col-sm-8">
					<select class="form-select" name="admin_rank" id="admin_rank">
						<option value="true">true</option>
						<option value="false" selected="selected">false</option>
					</select>
				</div>

				<div class="col-sm-4">
					<label class="col-form-label" for="admin_support_msgs">Include support messages</label>
				</div>
				<div class="col-sm-8">
					<select class="form-select" name="admin_support_msgs" id="admin_support_msgs">
						<option value="true" selected="selected">true</option>
						<option value="false">false</option>
					</select>
				</div>

				<div class="col-sm-4">
					<label class="col-form-label" for="admin_raidable">Allow admin to be raided</label>
				</div>
				<div class="col-sm-8">
					<select class="form-select" name="admin_raidable" id="admin_raidable">
						<option value="true" selected="selected">true</option>
						<option value="false">false</option>
					</select>
				</div>
			</div>

			<div class="form-text mt-3">This will add a first user and set them up as an Admin. You can leave this section empty.</div>
		</div>
	</div>

	<div class="d-grid d-sm-flex gap-2">
		<input type="submit" class="btn btn-primary" name="Submit" id="Submit" value="Submit">
		<a class="btn btn-outline-secondary" href="?s=3&t=<?php echo isset($_GET['t']) ? (int) $_GET['t'] : 1; ?><?php echo (isset($_GET['rtl']) && $_GET['rtl'] === '1') ? '&rtl=1' : ''; ?>">Back</a>
	</div>
</form>

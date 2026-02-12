<?php

include_once __DIR__ . '/../../GameEngine/config.php';

if(isset($_GET['c']) && $_GET['c'] == 1) {
	echo '<div class="alert alert-danger">Error importing database. Check configuration.</div>';
}

if(isset($_GET['err']) && $_GET['err'] == 1) {
	echo '<div class="alert alert-warning">Existing structure was found in the database! Please remove old game tables with the <strong>' . TB_PREFIX . '</strong> prefix from the <strong>' . SQL_DB . '</strong> database before continuing.</div>';
}
?>
<form action="process.php?t=<?php echo isset($_GET['t']) ? (int) $_GET['t'] : 1; ?><?php echo (isset($_GET['rtl']) && $_GET['rtl'] === '1') ? '&rtl=1' : ''; ?>" method="post" id="dataform" onsubmit="return proceed();">
	<input type="hidden" name="substruc" value="1">

	<h2 class="h5 mb-3">Create Database Structure</h2>

	<div class="alert alert-info">
		This can take some time. Please wait until the next page has been loaded.
	</div>

	<div class="d-grid d-sm-flex gap-2">
		<input type="submit" class="btn btn-primary" name="Submit" id="Submit" value="Create..." />
		<a class="btn btn-outline-secondary" href="?s=1&t=<?php echo isset($_GET['t']) ? (int) $_GET['t'] : 1; ?><?php echo (isset($_GET['rtl']) && $_GET['rtl'] === '1') ? '&rtl=1' : ''; ?>">Back</a>
	</div>
</form>

<div class="alert alert-success">
	<div class="fw-semibold">Thanks for installing TravianZ.</div>
	<div class="mt-1">All the files are placed and the database is created.</div>
</div>

<div class="alert alert-warning">
	<div class="fw-semibold">Please remove/rename the installation folder.</div>
	<div class="mt-1">The installer will rename itself automatically on this step.</div>
</div>

<div class="card mb-4">
	<div class="card-header">After Installation</div>
	<div class="card-body">
		<ul class="mb-0">
			<li>Delete install folder (sudo rm -R install)</li>
			<li>CHMOD GameEngine back to 755 (sudo chmod -R 755 GameEngine)</li>
			<li>CHMOD Prevention to 777 (sudo chmod -R 777 GameEngine/Prevention)</li>
			<li>CHMOD Notes to 777 (sudo chmod -R 777 GameEngine/Notes)</li>
			<li>CHMOD var/log to 777 (sudo chmod -R 777 var/log)</li>
			<li>Protect folder /Admin with password protection</li>
		</ul>
	</div>
</div>

<?php include __DIR__ . "/../../GameEngine/config.php";
$time = time();
rename(__DIR__ . "/..", __DIR__ . "/../../installed_".$time);
touch(__DIR__ . "/../../var/installed");
?>
<div class="d-grid d-sm-flex gap-2 align-items-center">
	<a class="btn btn-success" href="<?php echo htmlspecialchars(HOMEPAGE, ENT_QUOTES, 'UTF-8'); ?>">Go to homepage</a>
	<span class="text-body-secondary small">Installer folder renamed. Marker created in var/installed.</span>
</div>

<div class="card mt-4">
	<div class="card-header">Support</div>
	<div class="card-body">
		<div class="mb-3">Please support our developers and donate.</div>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="QHUTVY5MLECFQ">
			<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="Donate">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
		</form>
	</div>
</div>

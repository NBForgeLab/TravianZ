<div class="mb-4">
	<h2 class="h5 mb-3">Disclaimer</h2>
	<div class="alert alert-warning">
		<ul class="mb-0">
			<li>You are responsible for any legal results related to unlicensed content.</li>
			<li>No team is responsible for any damage done to your computer/server system.</li>
			<li>Review the code on your own behalf before using it in production.</li>
			<li><strong>You have no rights to edit copyright notices or claim this script as your own.</strong></li>
		</ul>
	</div>
</div>

<div class="mb-4">
	<h2 class="h5 mb-3">Before Installation</h2>
	<div class="card">
		<div class="card-body">
			<div class="fw-semibold mb-2">Linux permissions</div>
			<ul class="mb-0">
				<li>CHMOD install to 777 (chmod -R 777 install)</li>
				<li>CHMOD GameEngine to 777 (chmod -R 777 GameEngine)</li>
			</ul>
		</div>
	</div>
</div>

<div class="mb-4">
	<h2 class="h5 mb-3">After Installation</h2>
	<div class="card">
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
</div>

<div class="d-flex align-items-center justify-content-between">
	<div class="text-body-secondary">TravianZ Team</div>
	<a class="btn btn-primary" href="?s=1&t=<?php echo isset($_GET['t']) ? (int) $_GET['t'] : 1; ?><?php echo (isset($_GET['rtl']) && $_GET['rtl'] === '1') ? '&rtl=1' : ''; ?>">Next</a>
</div>

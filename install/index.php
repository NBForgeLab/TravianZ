<?php
if(PHP_VERSION_ID < 80200) {
    http_response_code(500);
    exit('This script requires PHP 8.2+');
}

// don't let SQL time out when 30-500 seconds (depending on php.ini) is not enough
@set_time_limit(0);

$step = isset($_GET['s']) ? (int) $_GET['s'] : 0;
$step = max(0, min(5, $step));
$rtl = isset($_GET['rtl']) && $_GET['rtl'] === '1';
$tz = isset($_GET['t']) ? (int) $_GET['t'] : 1;
switch ($tz) {
	case 1: $t_zone = "Africa/Dakar"; break;
	case 2: $t_zone = "America/New_York"; break;
	case 3: $t_zone = "Antarctica/Casey"; break;
	case 4: $t_zone = "Arctic/Longyearbyen"; break;
	case 5: $t_zone = "Asia/Kuala_Lumpur"; break;
	case 6: $t_zone = "Atlantic/Azores"; break;
	case 7: $t_zone = "Australia/Melbourne"; break;
	case 8: $t_zone = "Europe/Bucharest"; break;
	case 9: $t_zone = "Europe/London"; break;
	case 10: $t_zone = "Europe/Bratislava"; break;
	case 11: $t_zone = "Indian/Maldives"; break;
	case 12: $t_zone = "Pacific/Fiji"; break;
	default:
		$tz = 1;
		$t_zone = "Africa/Dakar";
		break;
}
date_default_timezone_set($t_zone);
?>

<!doctype html>
<html lang="en" dir="<?php echo $rtl ? 'rtl' : 'ltr'; ?>">
<head>
	<title>TravianZ Installation</title>
	<link rel="shortcut icon" href="favicon.ico" />
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link href="../assets/vendor/bootstrap/5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
	<link href="../assets/css/installer.css" rel="stylesheet" />
</head>
<body>
<?php
$rootPath = dirname(__DIR__);
$configPath = $rootPath . "/GameEngine/config.php";
$configDirWritable = is_writable($rootPath . "/GameEngine");
$configFileWritable = !file_exists($configPath) || is_writable($configPath);
$isInstalled = file_exists($rootPath . "/var/installed");
$steps = [
	0 => "Intro",
	1 => "Configuration",
	2 => "Database",
	3 => "World Data",
	4 => "Accounts",
	5 => "End",
];
$stepCount = count($steps);
$progressPercent = round((($step + 1) / $stepCount) * 100);
?>

<div class="container py-4">
	<div class="row justify-content-center">
		<div class="col-12 col-xl-10">
			<div class="d-flex align-items-center justify-content-between mb-3">
				<div>
					<h1 class="h4 mb-0">TravianZ Installation</h1>
					<div class="text-body-secondary">Step <?php echo ($step + 1); ?> of <?php echo $stepCount; ?></div>
				</div>
				<div class="text-end text-body-secondary small">
					<div>Timezone: <?php echo htmlspecialchars($t_zone, ENT_QUOTES, 'UTF-8'); ?></div>
					<div class="mt-2">
						<?php
						$toggleRtlUrl = '?s=' . $step . '&t=' . $tz . ($rtl ? '' : '&rtl=1');
						?>
						<a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars($toggleRtlUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $rtl ? 'LTR' : 'RTL'; ?></a>
					</div>
				</div>
			</div>

			<div class="progress mb-4" role="progressbar" aria-label="Installation progress" aria-valuenow="<?php echo $progressPercent; ?>" aria-valuemin="0" aria-valuemax="100">
				<div class="progress-bar" style="width: <?php echo $progressPercent; ?>%"></div>
			</div>

			<div class="row g-4">
				<div class="col-12 col-lg-4">
					<div class="card">
						<div class="card-header">Steps</div>
						<div class="card-body p-0">
							<?php include __DIR__ . "/templates/menu.tpl"; ?>
						</div>
					</div>
				</div>
				<div class="col-12 col-lg-8">
					<div class="card">
						<div class="card-body">
							<?php
							if (!$configDirWritable || !$configFileWritable) {
								echo '<div class="alert alert-danger mb-0">It\'s not possible to write the config file. Please ensure that <strong>GameEngine/</strong> and <strong>GameEngine/config.php</strong> are writable, then refresh this page.</div>';
							} else if ($isInstalled) {
								echo '<div class="alert alert-warning mb-0">Installation appears to have been completed. If this is an error remove <strong>var/installed</strong> in the project root.</div>';
							} else {
								switch ($step) {
									case 0:
										include __DIR__ . "/templates/greet.tpl";
										break;
									case 1:
										include __DIR__ . "/templates/config.tpl";
										break;
									case 2:
										include __DIR__ . "/templates/dataform.tpl";
										break;
									case 3:
										include __DIR__ . "/templates/wdata.tpl";
										break;
									case 4:
										include __DIR__ . "/templates/accounts.tpl";
										break;
									case 5:
										include __DIR__ . "/templates/end.tpl";
										break;
								}
							}
							?>
						</div>
					</div>
					<div class="text-center text-body-secondary small mt-3">&copy; 2010 - <?php echo date('Y'); ?> TravianZ</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
function refresh(tz) {
	var dt = tz.split(",");
	var tzId = dt[0];
	location = "?s=1&t=" + encodeURIComponent(tzId) + "<?php echo $rtl ? '&rtl=1' : ''; ?>";
}

function proceed() {
	var e = document.getElementById('Submit');
	if (!e) return true;
	setTimeout(function () {
		e.disabled = true;
	}, 200);
	e.value = "Processing...";
	return true;
}

document.addEventListener('change', function (e) {
	var el = e.target;
	if (!el) return;
	var cb = el.getAttribute('data-change-callback');
	if (cb && typeof window[cb] === 'function') {
		window[cb](el.value);
	}
});
</script>
<script src="../assets/vendor/bootstrap/5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// install/wdata.tpl

include_once __DIR__ . '/../../GameEngine/config.php';

if (isset($_GET['c']) && $_GET['c'] == '1') {
    echo '<div class="alert alert-danger">Error creating world data. Check configuration or file.</div>';
}
if (isset($_GET['err']) && $_GET['err'] == '1') {
    echo '<div class="alert alert-warning">Existing World Data found in the database! Please empty tables <strong>'
        . TB_PREFIX . 'odata</strong>, <strong>' . TB_PREFIX . 'units</strong>, <strong>' . TB_PREFIX . 'vdata</strong>, <strong>' . TB_PREFIX . 'wdata</strong> before continuing.</div>';
}

$autoStartCroppers = isset($_GET['startCroppers']) && $_GET['startCroppers'] === '1';
?>

<form action="process.php?t=<?php echo isset($_GET['t']) ? (int) $_GET['t'] : 1; ?><?php echo (isset($_GET['rtl']) && $_GET['rtl'] === '1') ? '&rtl=1' : ''; ?>" method="post" id="dataform" onsubmit="return proceed();">
    <input type="hidden" name="subwdata" value="1" />

	<h2 class="h5 mb-3">Create World Data</h2>

	<div class="alert alert-info">
		This can take some time. Please wait until the next page has been loaded.
	</div>

	<div id="submitWrap" class="<?php echo $autoStartCroppers ? 'd-none' : ''; ?>">
		<div class="d-grid d-sm-flex gap-2">
			<input type="submit" class="btn btn-primary" name="Submit" id="Submit" value="Create..." />
			<a class="btn btn-outline-secondary" href="?s=2&t=<?php echo isset($_GET['t']) ? (int) $_GET['t'] : 1; ?><?php echo (isset($_GET['rtl']) && $_GET['rtl'] === '1') ? '&rtl=1' : ''; ?>">Back</a>
		</div>
	</div>

	<div id="progressBox" class="<?php echo $autoStartCroppers ? '' : 'd-none'; ?> mt-4">
		<div class="fw-semibold mb-2">Building croppers</div>
		<div class="progress" role="progressbar" aria-label="Croppers progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
			<div id="pbar" class="progress-bar" style="width: 0%"></div>
		</div>
		<div id="pinfo" class="small text-body-secondary mt-2">Starting…</div>
		<pre id="plog" class="mt-3 p-3 bg-body-tertiary border rounded small" style="max-height: 220px; overflow: auto;"></pre>
		<div id="autoNext" class="small text-body-secondary mt-2 d-none">
			Proceeding to next step in <strong id="cd">3</strong>…
		</div>
	</div>

	<script>
	(function () {
		var NEXT_URL = 'index.php?s=4&t=<?php echo isset($_GET['t']) ? (int) $_GET['t'] : 1; ?>';
		<?php if (isset($_GET['rtl']) && $_GET['rtl'] === '1') { echo "NEXT_URL += '&rtl=1';"; } ?>
		var COUNTDOWN_SECS = 3;
		var finished = false;

		function startCountdown() {
			var box = document.getElementById('autoNext');
			var cdEl = document.getElementById('cd');
			var left = COUNTDOWN_SECS;
			box.classList.remove('d-none');
			cdEl.textContent = left;
			var t = setInterval(function () {
				left--;
				cdEl.textContent = left;
				if (left <= 0) {
					clearInterval(t);
					window.location.href = NEXT_URL;
				}
			}, 1000);
		}

		function startCroppersBuild() {
			var box = document.getElementById('progressBox');
			var pbar = document.getElementById('pbar');
			var pinfo = document.getElementById('pinfo');
			var plog = document.getElementById('plog');

			var submitWrap = document.getElementById('submitWrap');
			if (submitWrap) submitWrap.classList.add('d-none');
			box.classList.remove('d-none');

			if (!('EventSource' in window)) {
				plog.textContent += "Your browser does not support live progress.\n";
				return;
			}

			var MAX_RETRIES = 3;
			var retries = 0;
			var es = new EventSource('ajax_croppers.php');

			function logLine(line) {
				plog.textContent += line + "\n";
				plog.scrollTop = plog.scrollHeight;
			}

			es.onmessage = function (e) {
				if (!e.data || e.data.charCodeAt(0) !== 123) return;

				try {
					var d = JSON.parse(e.data);
					var pct = (d.pct || 0) | 0;
					var done = (d.done || 0) | 0;
					var total = (d.total || 0) | 0;

					if (finished) return;
					retries = 0;

					pbar.style.width = pct + '%';
					pbar.parentElement.setAttribute('aria-valuenow', String(pct));
					pinfo.textContent = done + ' / ' + total + ' (' + pct + '%)';

					if (d.msg) logLine(String(d.msg));

					if (d.error) {
						finished = true;
						logLine(String(d.msg || 'Server reported an error.'));
						es.close();
						startCountdown();
						return;
					}

					if (pct >= 100) {
						finished = true;
						logLine('Completed.');
						es.close();
						startCountdown();
					}
				} catch (_) {
				}
			};

			es.onerror = function () {
				if (finished) return;
				retries++;
				logLine('Connection hiccup (' + retries + '/' + MAX_RETRIES + '), retrying…');

				if (retries >= MAX_RETRIES) {
					finished = true;
					logLine('Too many connection failures — skipping croppers build.');
					es.close();
					startCountdown();
				}
			};
		}

		document.addEventListener('DOMContentLoaded', function () {
			<?php if ($autoStartCroppers) { echo 'startCroppersBuild();'; } ?>
		});
	})();
	</script>
</form>

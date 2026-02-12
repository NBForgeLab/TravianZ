<?php



	$step = isset($_GET['s']) ? (int) $_GET['s'] : 0;
	$step = max(0, min(5, $step));
	$rtl = isset($_GET['rtl']) && $_GET['rtl'] === '1';
	$steps = [
		0 => 'Intro',
		1 => 'Configuration',
		2 => 'Database',
		3 => 'World Data',
		4 => 'Accounts',
		5 => 'End',
	];

	echo '<div class="list-group list-group-flush">';
	foreach ($steps as $i => $label) {
		$isActive = ($i === $step);
		$isCompleted = ($i < $step);
		$isDisabled = ($i > $step);

		$classes = 'list-group-item list-group-item-action d-flex align-items-center justify-content-between';
		if ($isActive) {
			$classes .= ' active';
		}
		if ($isDisabled) {
			$classes .= ' disabled';
		}

		$href = $isDisabled ? '#' : ('?s=' . $i . '&t=' . (isset($_GET['t']) ? (int) $_GET['t'] : 1) . ($rtl ? '&rtl=1' : ''));

		echo '<a class="' . $classes . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" aria-current="' . ($isActive ? 'step' : 'false') . '">';
		echo '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
		if ($isCompleted) {
			echo '<span class="badge text-bg-success">Done</span>';
		} elseif ($isActive) {
			echo '<span class="badge text-bg-light">Current</span>';
		} else {
			echo '<span class="badge text-bg-secondary">Next</span>';
		}
		echo '</a>';
	}
	echo '</div>';

?>

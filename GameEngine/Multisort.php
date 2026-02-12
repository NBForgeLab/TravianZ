<?php


class multiSort {

	function sorte($array)
	{
		$criteria = [];
		$argsCount = func_num_args();
		for($i = 1; $i < $argsCount; $i += 3)
		{
			$key = func_get_arg($i);
			$order = ($i + 1 < $argsCount) ? func_get_arg($i + 1) : true;
			$type = ($i + 2 < $argsCount) ? func_get_arg($i + 2) : 0;

			$criteria[] = [$key, (bool) $order, (int) $type];
		}

		if (class_exists(\App\Legacy\MultiSorter::class) || $this->tryRequireAutoloader()) {
			return \App\Legacy\MultiSorter::sort($array, $criteria);
		}

		usort($array, function($a, $b) use ($criteria)
		{
			foreach($criteria as $criterion)
			{
				$key = $criterion[0];
				$order = $criterion[1];
				$type = $criterion[2];

				$av = isset($a[$key]) ? $a[$key] : null;
				$bv = isset($b[$key]) ? $b[$key] : null;

				switch($type)
				{
					case 1:
						$result = strnatcasecmp((string) $av, (string) $bv);
						break;
					case 2:
						$result = ((float) $av) <=> ((float) $bv);
						break;
					case 3:
						$result = strcmp((string) $av, (string) $bv);
						break;
					case 4:
						$result = strcasecmp((string) $av, (string) $bv);
						break;
					default:
						$result = strnatcmp((string) $av, (string) $bv);
						break;
				}

				if ($result !== 0) {
					return $result * ($order ? 1 : -1);
				}
			}

			return 0;
		});
		return $array;
	}

	private function tryRequireAutoloader()
	{
		for ($i = 0; $i < 5; $i++) {
			$prefix = str_repeat('../', $i);
			$autoloader = $prefix . 'autoloader.php';
			if (file_exists($autoloader)) {
				require_once $autoloader;
				return true;
			}
		}
		return false;
	}

};
$multisort = new multiSort;
?>

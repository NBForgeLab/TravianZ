<?php
declare(strict_types=1);

define('TRAVIANZ_AUTOLOAD_THROW_ON_MISS', false);

require __DIR__ . '/../autoloader.php';

\App\Legacy\LegacyEnvironment::defineTbPrefix();

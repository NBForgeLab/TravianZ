<?php

declare(strict_types=1);

require __DIR__ . '/support/bootstrap.php';

use TravianZ\Tests\AdminE2E\TestRunner;

$runner = new TestRunner();

require __DIR__ . '/tests/admin_page_loads.php';
require __DIR__ . '/tests/admin_login_db.php';
require __DIR__ . '/tests/settings_server.php';
require __DIR__ . '/tests/settings_plus.php';
require __DIR__ . '/tests/settings_log.php';
require __DIR__ . '/tests/settings_newsbox.php';
require __DIR__ . '/tests/settings_extra.php';

exit($runner->run());


<?php
// Mocking necessary constants and environment
define('SQL_SERVER', 'localhost');
define('SQL_USER', 'root');
define('SQL_PASS', '');
define('SQL_DB', 'travianz_test_db');
define('SQL_PREFIX', 'tz_');

// The initialization logic
$initializedFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'travianz_test_initialized_test';
// Clean up from previous run
if (file_exists($initializedFile)) unlink($initializedFile);

$lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'travianz_test.lock';

// Simulation of parallel process
// I'll create a script that just tries to acquire the lock and do the work
$script = '
<?php
$initializedFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "travianz_test_initialized_test";
$lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "travianz_test.lock";

$fp = fopen($lockFile, "c");
if (flock($fp, LOCK_EX)) {
    if (!file_exists($initializedFile)) {
        // Simulate DB init delay
        sleep(2);
        touch($initializedFile);
        echo "Initialized by " . getmypid() . "\n";
    } else {
        echo "Already initialized, skipped by " . getmypid() . "\n";
    }
    flock($fp, LOCK_UN);
}
fclose($fp);
';
file_put_contents('worker.php', $script);

// Run two instances
exec('php worker.php > out1.txt & php worker.php > out2.txt');
echo "Started workers.\n";

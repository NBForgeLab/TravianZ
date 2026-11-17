<?php
require_once __DIR__ . '/../GameEngine/config.test.php';

$mysqli = new mysqli(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = file_get_contents(__DIR__ . '/../var/db/struct.sql');
$sql = str_replace('%PREFIX%', SQL_PREFIX, $sql);

// Disable foreign key checks for clean import
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

// The multi_query might fail on complex scripts, let's try a different approach if needed.
// For now, let's execute the queries one by one or trust multi_query.
if ($mysqli->multi_query($sql)) {
    do {
        // consume results
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Database reset successfully.";
} else {
    echo "Error resetting database: " . $mysqli->error;
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
$mysqli->close();
?>

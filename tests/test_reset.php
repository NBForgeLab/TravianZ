<?php
require_once __DIR__ . '/../GameEngine/config.test.php';
$mysqli = new mysqli(SQL_SERVER, SQL_USER, SQL_PASS);
if ($mysqli->connect_error) { die("Conn fail: " . $mysqli->connect_error); }

echo "Dropping " . SQL_DB . "\n";
if (!$mysqli->query("DROP DATABASE IF EXISTS `" . SQL_DB . "`")) { die("Drop fail: " . $mysqli->error); }

echo "Creating " . SQL_DB . "\n";
if (!$mysqli->query("CREATE DATABASE `" . SQL_DB . "`")) { die("Create fail: " . $mysqli->error); }

echo "Selecting " . SQL_DB . "\n";
if (!$mysqli->select_db(SQL_DB)) { die("Select fail: " . $mysqli->error); }

$sql = file_get_contents(__DIR__ . '/../var/db/struct.sql');
$sql = str_replace('%PREFIX%', SQL_PREFIX, $sql);

echo "Running multi_query\n";
if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
} else {
    die("Multi query fail: " . $mysqli->error);
}
echo "Done\n";
?>

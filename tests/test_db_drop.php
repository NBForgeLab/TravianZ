<?php
require_once __DIR__ . '/../GameEngine/config.test.php';
$mysqli = new mysqli(SQL_SERVER, SQL_USER, SQL_PASS);
if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error;
} else {
    echo "Connected successfully!\n";
    if ($mysqli->query("DROP DATABASE IF EXISTS `" . SQL_DB . "`")) {
        echo "Database dropped!\n";
    } else {
        echo "Failed to drop database: " . $mysqli->error . "\n";
    }
}
?>
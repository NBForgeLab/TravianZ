<?php
require_once __DIR__ . '/../GameEngine/config.test.php';
$mysqli = new mysqli(SQL_SERVER, SQL_USER, SQL_PASS);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
echo "Connected successfully\n";

$dbName = SQL_DB;
$sql = "DROP DATABASE IF EXISTS `" . $dbName . "`";
if ($mysqli->query($sql)) {
    echo "Database dropped\n";
} else {
    echo "Failed to drop database: " . $mysqli->error . "\n";
}

$sql = "CREATE DATABASE `" . $dbName . "`";
if ($mysqli->query($sql)) {
    echo "Database created\n";
} else {
    echo "Failed to create database: " . $mysqli->error . "\n";
}

$mysqli->close();
?>

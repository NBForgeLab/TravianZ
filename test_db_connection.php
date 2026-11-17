<?php
$servername = "localhost";
$username = "travianz_tester";
$password = "tester_password";
$dbname = "travianz_test";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Connected successfully to database: " . $dbname;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
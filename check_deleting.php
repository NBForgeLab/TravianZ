<?php
require_once 'tests/bootstrap.php';
$res = $GLOBALS['database']->query_return("SELECT * FROM tz_deleting");
var_dump($res);
?>
<?php

require_once "dbcon.php";

$database = new Database();
$dbCon = $database->connect();

echo "Database: " . $dbCon->query(
    "SELECT DATABASE()"
)->fetchColumn();


<?php

// Include the database connection file
require_once "dbcon.php";

// Create the Database object
$database = new Database();

// Create the PDO connection
$dbCon = $database->connect();

// Show a message if the connection works
echo "Database connected successfully!";
<?php

// Include the database connection file
require_once "dbcon.php";

// Create a connection to the database
$dbCon = dbCon($user, $pass);

// Show a message if the connection works
echo "Database connected successfully!";
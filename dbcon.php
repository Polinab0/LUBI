<?php

// Database login information
$user = "root";
$pass = "";

// Create a connection to the LUBI database
function dbCon($user, $pass) {

    try {

        $dbCon = new PDO(
            'mysql:host=localhost;dbname=LUBI;charset=utf8',
            $user,
            $pass
        );

        return $dbCon;

    } catch (PDOException $err) {

        echo "Error!: " . $err->getMessage() . "<br/>";
        die();

    }
}
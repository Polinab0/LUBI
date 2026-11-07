<?php

class Database {

    private $dbCon;

    // Create a connection to the database
    public function connect() {

        try {

            // Check if the project is running locally
            if (
                $_SERVER['HTTP_HOST'] === 'localhost'
                || $_SERVER['HTTP_HOST'] === '127.0.0.1'
            ) {

                $host = 'localhost';
                $dbName = 'LUBI';
                $user = 'root';
                $pass = '';

            } else {

                // Load production database settings
                $config = require "config.php";

                $host = $config['host'];
                $dbName = $config['name'];
                $user = $config['username'];
                $pass = $config['password'];
            }

            $this->dbCon = new PDO(
                "mysql:host=$host;dbname=$dbName;charset=utf8",
                $user,
                $pass
            );

            return $this->dbCon;

        } catch (PDOException $err) {

            echo "Error!: " . $err->getMessage() . "<br/>";
            die();
        }
    }
}
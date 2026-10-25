<?php

class Database {

    private $user = "root";
    private $pass = "";
    private $dbCon;

    // Create a connection to the LUBI database
    public function connect() {

        try {

            $this->dbCon = new PDO(
                'mysql:host=localhost;dbname=LUBI;charset=utf8',
                $this->user,
                $this->pass
            );

            return $this->dbCon;

        } catch (PDOException $err) {

            echo "Error!: " . $err->getMessage() . "<br/>";
            die();

        }
    }
}
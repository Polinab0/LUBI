<?php

class User {

    private $dbCon;

    // Save the database connection inside the class
    public function __construct($dbCon) {
        $this->dbCon = $dbCon;
    }


    // Create a new user
    public function register(
        $userType,
        $username,
        $password,
        $email,
        $city,
        $bio
    ) {

        // Hash the password before saving it in the database
        $hashedPassword = password_hash(
            $password,
            PASSWORD_BCRYPT
        );

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "INSERT INTO user
            (userType, username, password, email, city, bio)
            VALUES
            (:userType, :username, :password, :email, :city, :bio)"
        );

        // Bind the values to the SQL placeholders
        $query->bindParam(':userType', $userType);
        $query->bindParam(':username', $username);
        $query->bindParam(':password', $hashedPassword);
        $query->bindParam(':email', $email);
        $query->bindParam(':city', $city);
        $query->bindParam(':bio', $bio);

        // Execute the query
        return $query->execute();
    }


    // Login a user with email and password
    public function login(
        $email,
        $password
    ) {

        // Find the user by email
        $query = $this->dbCon->prepare(
            "SELECT * FROM user
            WHERE email = :email"
        );

        // Bind the email
        $query->bindParam(':email', $email);

        // Execute the query
        $query->execute();

        // Get the user
        $foundUser = $query->fetch(PDO::FETCH_ASSOC);

        // Check if the user exists
        if ($foundUser) {

            // Check if the password is correct
            if (password_verify(
                $password,
                $foundUser['password']
            )) {

                return $foundUser;
            }
        }

        // Return false if login failed
        return false;
    }


    // Get one user by ID
    public function getUserById($userID) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "SELECT
                userID,
                userType,
                username,
                email,
                city,
                bio
            FROM user
            WHERE userID = :userID"
        );

        // Bind the user ID
        $query->bindParam(':userID', $userID);

        // Execute the query
        $query->execute();

        // Return the user
        return $query->fetch(PDO::FETCH_ASSOC);
    }


    // Update user profile information
    public function updateProfile(
        $userID,
        $username,
        $email,
        $city,
        $bio
    ) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "UPDATE user
            SET
                username = :username,
                email = :email,
                city = :city,
                bio = :bio
            WHERE userID = :userID"
        );

        // Bind the values
        $query->bindParam(':username', $username);
        $query->bindParam(':email', $email);
        $query->bindParam(':city', $city);
        $query->bindParam(':bio', $bio);
        $query->bindParam(':userID', $userID);

        // Execute the query
        return $query->execute();
    }
}
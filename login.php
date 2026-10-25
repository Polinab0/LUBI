<?php

// Include the database connection
require_once "dbcon.php";

// Include the User class
require_once "classes/User.php";

// Include the session functions
require_once "includes/session.php";

// Create the Database object
$database = new Database();

// Create the PDO connection
$dbCon = $database->connect();

// Create a User object
$userObject = new User($dbCon);

// Check if the login form was submitted
if (isset($_POST['submit'])) {

    // Get the form values
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Try to login the user
    $foundUser = $userObject->login(
        $email,
        $password
    );

    // Check if login was successful
    if ($foundUser) {

        // Save user information in the session
        $_SESSION['userID'] = $foundUser['userID'];
        $_SESSION['username'] = $foundUser['username'];
        $_SESSION['userType'] = $foundUser['userType'];

        // Redirect to the front page
        header("Location: index.php");
        exit();

    } else {

        $message = "Wrong email or password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Login</title>
</head>

<body>

    <h1>Login</h1>

    <?php
    if (isset($message)) {
        echo "<p>" . $message . "</p>";
    }
    ?>

    <form action="login.php" method="post">

        <label for="email">Email:</label>
        <input
            type="email"
            name="email"
            id="email"
            required
        >

        <br><br>

        <label for="password">Password:</label>
        <input
            type="password"
            name="password"
            id="password"
            required
        >

        <br><br>

        <input
            type="submit"
            name="submit"
            value="Login"
        >

    </form>

    <br>

    <a href="register.php">
        Don't have an account? Sign Up
    </a>

</body>

</html>
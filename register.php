<?php

// Include the database connection
require_once "dbcon.php";

// Include the User class
require_once "classes/User.php";

// Create a database connection
$dbCon = dbCon($user, $pass);

// Create a User object
$userObject = new User($dbCon);

// Check if the registration form was submitted
if (isset($_POST['submit'])) {

    // Get form values
    $userType = $_POST['userType'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $city = $_POST['city'];
    $bio = $_POST['bio'];

    // Register the new user
    $userObject->register(
        $userType,
        $username,
        $password,
        $email,
        $city,
        $bio
    );

    // Redirect to the login page after registration
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Register</title>
</head>

<body>

    <h1>Create Account</h1>

    <form action="register.php" method="post">

        <label for="userType">User Type:</label>
        <select name="userType" id="userType" required>
            <option value="learner">Learner</option>
            <option value="professional">Professional</option>
        </select>

        <br><br>

        <label for="username">Username:</label>
        <input
            type="text"
            name="username"
            id="username"
            required
        >

        <br><br>

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

        <label for="city">City:</label>
        <input
            type="text"
            name="city"
            id="city"
        >

        <br><br>

        <label for="bio">Bio:</label>
        <textarea
            name="bio"
            id="bio"
        ></textarea>

        <br><br>

        <input
            type="submit"
            name="submit"
            value="Sign Up"
        >

    </form>

    <br>

    <a href="login.php">Already have an account? Login</a>

</body>

</html>
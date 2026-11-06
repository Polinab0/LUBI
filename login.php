
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

    // Check the CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // Get the form values
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email is not valid.");
    }

    // Try to login the user
    $foundUser = $userObject->login(
        $email,
        $password
    );

    // Check if login was successful
    if ($foundUser) {

        // Create a new session ID for security
        session_regenerate_id(true);

        // Clear old login information
        unset(
            $_SESSION['userID'],
            $_SESSION['username'],
            $_SESSION['userType']
        );

        // Save the correct user's information
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
        echo "<p>" . htmlspecialchars(
            $message,
            ENT_QUOTES,
            'UTF-8'
        ) . "</p>";
    }
    ?>

    <form action="login.php" method="post">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo csrf_token(); ?>"
        >

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

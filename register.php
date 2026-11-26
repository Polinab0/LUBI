<?php

// Include the database connection
require_once "dbcon.php";
// Include the session functions
require_once "includes/session.php";

// Include the User class
require_once "classes/User.php";

// Create the Database object
$database = new Database();

// Create the PDO connection
$dbCon = $database->connect();

// Create a User object
$userObject = new User($dbCon);

// Check if the registration form was submitted
if (isset($_POST['submit'])) {
    // Check the CSRF token
if (
    !isset($_POST['csrf_token'])
    || !verify_csrf_token($_POST['csrf_token'])
) {
    die("Invalid CSRF token.");
}

    // Get form values
    $userType = $_POST['userType'];
    // Check if user type is valid
    $allowedUserTypes = [
        'learner',
        'professional'
    ];

if (!in_array($userType, $allowedUserTypes)) {
    die("Invalid user type.");
}
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $bio = trim($_POST['bio']);

    // Check if username is empty
if (empty($username)) {
    die("Username is required.");
}

// Check if email is empty
if (empty($email)) {
    die("Email is required.");
}

// Check if email is valid
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email is not valid.");
}

// Check password length
if (strlen($password) < 8) {
    die("Password must be at least 8 characters.");
}

// Check bio length
if (strlen($bio) > 500) {
    die("Bio cannot be longer than 500 characters.");
}

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

       <input
          type="hidden"
          name="csrf_token"
          value="<?php echo csrf_token(); ?>"
       >

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
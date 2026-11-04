<?php

// Include the session functions
require_once "includes/session.php";

// Check if the user is logged in
confirm_logged_in();

// Include the database connection
require_once "dbcon.php";

// Include the User class
require_once "classes/User.php";

// Create the Database object
$database = new Database();

// Create the PDO connection
$dbCon = $database->connect();

// Create a User object
$userObject = new User($dbCon);

// Get the logged in user ID
$userID = $_SESSION['userID'];

// Get current user information
$currentUser = $userObject->getUserById($userID);

// Create an array for validation errors
$errors = [];

if (isset($_POST['submit'])) {

     // Check the CSRF token
    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $bio = trim($_POST['bio']);

    // Check if username is empty
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    // Check if email is empty
    if (empty($email)) {
        $errors[] = "Email is required.";
    }

    // Check if email is valid
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid.";
    }

    // Check bio length
    if (strlen($bio) > 500) {
        $errors[] = "Bio cannot be longer than 500 characters.";
    }

    // Update profile only if there are no errors
    if (empty($errors)) {

        $userObject->updateProfile(
            $userID,
            $username,
            $email,
            $city,
            $bio
        );

        $_SESSION['username'] = $username;

        header("Location: profile.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Edit Profile</title>
</head>

<body>

    <h1>Edit Profile</h1>

    <?php if (!empty($errors)) { ?>

    <?php foreach ($errors as $error) { ?>

        <p>
            <?php echo htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </p>

     <?php } ?>

    <?php } ?>

    <form
    action="editProfile.php"
    method="post"
>

    <input
        type="hidden"
        name="csrf_token"
        value="<?php echo csrf_token(); ?>"
    >

        <label for="username">Username:</label>

        <input
            type="text"
            name="username"
            id="username"
            value="<?php echo htmlspecialchars($username ?? $currentUser['username'], ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <br><br>

        <label for="email">Email:</label>

        <input
            type="email"
            name="email"
            id="email"
            value="<?php echo htmlspecialchars($email ?? $currentUser['email'], ENT_QUOTES, 'UTF-8'); ?>" required
        >

        <br><br>

        <label for="city">City:</label>

        <input
            type="text"
            name="city"
            id="city"
            value="<?php echo htmlspecialchars($city ?? $currentUser['city'], ENT_QUOTES, 'UTF-8'); ?>"
        >

        <br><br>

        <label for="bio">Bio:</label>

        <textarea
            name="bio"
            id="bio"
        ><?php echo htmlspecialchars($bio ?? $currentUser['bio'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <br><br>

        <input
            type="submit"
            name="submit"
            value="Save Changes"
        >

    </form>

    <br>

    <a href="profile.php">
        Cancel
    </a>

</body>

</html>
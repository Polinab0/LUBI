<?php

// Include the session functions
require_once "includes/session.php";

// Check if the user is logged in
confirm_logged_in();

// Include the database connection
require_once "dbcon.php";

// Include the User class
require_once "classes/User.php";

// Create a database connection
$dbCon = dbCon($user, $pass);

// Create a User object
$userObject = new User($dbCon);

// Get the logged in user ID
$userID = $_SESSION['userID'];

// Get current user information
$currentUser = $userObject->getUserById($userID);


// Check if the form was submitted
if (isset($_POST['submit'])) {

    // Get form values
    $username = $_POST['username'];
    $email = $_POST['email'];
    $city = $_POST['city'];
    $bio = $_POST['bio'];

    // Update the user profile
    $userObject->updateProfile(
        $userID,
        $username,
        $email,
        $city,
        $bio
    );

    // Update username in the session
    $_SESSION['username'] = $username;

    // Redirect back to profile
    header("Location: profile.php");
    exit();
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

    <form
        action="editProfile.php"
        method="post"
    >

        <label for="username">Username:</label>

        <input
            type="text"
            name="username"
            id="username"
            value="<?php echo $currentUser['username']; ?>"
            required
        >

        <br><br>

        <label for="email">Email:</label>

        <input
            type="email"
            name="email"
            id="email"
            value="<?php echo $currentUser['email']; ?>"
            required
        >

        <br><br>

        <label for="city">City:</label>

        <input
            type="text"
            name="city"
            id="city"
            value="<?php echo $currentUser['city']; ?>"
        >

        <br><br>

        <label for="bio">Bio:</label>

        <textarea
            name="bio"
            id="bio"
        ><?php echo $currentUser['bio']; ?></textarea>

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
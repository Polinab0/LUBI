
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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email is not valid.";
    } else {

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
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LUBI - Login</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-page">

    <main class="login-container">

        <div class="login-card">

            <div class="login-header">
                <a href="index.php" class="login-logo">LUBI.</a>

                <p class="login-tagline">
                    Create. Learn. Grow. In Beauty.
                </p>
            </div>

            <h1>Welcome Back</h1>

            <p class="login-description">
                Log in to continue your beauty journey.
            </p>

            <?php if (isset($message)) { ?>
                <p class="login-error" role="alert">
                    <?php
                    echo htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </p>
            <?php } ?>

            <form action="login.php" method="post" class="login-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"
                >

                <div class="login-field">
                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="login-field">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" name="submit" class="login-button">
                    Log In
                </button>

            </form>

            <p class="login-register">
                Don't have an account?
                <a href="register.php">Sign Up</a>
            </p>

            <a href="index.php" class="login-back">
                Back to Home
            </a>

        </div>

    </main>

</body>

</html>


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

$message = "";

// Check if the registration form was submitted
if (isset($_POST['submit'])) {

    // Check the CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // SPAM PROTECTION 1: Honeypot
    // Bots may fill in this hidden field.
    if (!empty($_POST['website'] ?? '')) {
        die("Registration could not be completed.");
    }

    // SPAM PROTECTION 2: Rate limiting
    // Allow one registration attempt every 30 seconds
    // within the same PHP session.
    $currentTime = time();
    $cooldown = 30;

    if (
        isset($_SESSION['last_registration_attempt']) &&
        ($currentTime - $_SESSION['last_registration_attempt']) < $cooldown
    ) {
        $remainingTime = $cooldown -
            ($currentTime - $_SESSION['last_registration_attempt']);

        $message = "Please wait " . $remainingTime .
            " seconds before trying again.";

    } else {

        // Save the time of this registration attempt
        $_SESSION['last_registration_attempt'] = $currentTime;

        // Get form values
        $userType = $_POST['userType'] ?? '';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        // Check if user type is valid
        $allowedUserTypes = ['learner', 'professional'];

        if (!in_array($userType, $allowedUserTypes, true)) {
            $message = "Invalid user type.";

        } elseif (empty($username)) {
            $message = "Username is required.";

        } elseif (empty($email)) {
            $message = "Email is required.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Email is not valid.";

        } elseif (strlen($password) < 8) {
            $message = "Password must be at least 8 characters.";

        } elseif (strlen($bio) > 500) {
            $message = "Bio cannot be longer than 500 characters.";

        } else {

            // Register the new user
            try {
                $userObject->register(
                    $userType,
                    $username,
                    $password,
                    $email,
                    $city,
                    $bio
                );

                // Redirect to login
                header("Location: login.php");
                exit();

            } catch (PDOException $e) {
                $message = "Could not create account. Please check your details or try another email.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LUBI - Sign Up</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body class="register-page">

    <main class="register-container">

        <section class="register-card">

            <a href="index.php" class="register-logo">LUBI.</a>

            <p class="register-tagline">
                Create. Learn. Grow. In Beauty.
            </p>

            <h1>Create Account</h1>

            <p class="register-subtitle">
                Join our beauty community and start your journey.
            </p>

            <?php if (!empty($message)): ?>
                <p class="register-error">
                    <?php echo htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </p>
            <?php endif; ?>

            <form action="register.php" method="post">

                <!-- SPAM PROTECTION: Honeypot -->
                <div
                    style="position: absolute; left: -9999px;"
                    aria-hidden="true"
                >
                    <label for="website">Website</label>

                    <input
                        type="text"
                        name="website"
                        id="website"
                        tabindex="-1"
                        autocomplete="off"
                    >
                </div>

                <!-- CSRF protection -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars(
                        csrf_token(),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >

                <label for="userType">I want to join as</label>

                <select name="userType" id="userType" required>

                    <option
                        value="learner"
                        <?php echo (($userType ?? '') === 'learner') ? 'selected' : ''; ?>
                    >
                        Learner
                    </option>

                    <option
                        value="professional"
                        <?php echo (($userType ?? '') === 'professional') ? 'selected' : ''; ?>
                    >
                        Professional
                    </option>

                </select>

                <label for="username">Username</label>

                <input
                    type="text"
                    name="username"
                    id="username"
                    maxlength="100"
                    value="<?php echo htmlspecialchars(
                        $username ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                    required
                >

                <label for="email">Email Address</label>

                <input
                    type="email"
                    name="email"
                    id="email"
                    value="<?php echo htmlspecialchars(
                        $email ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                    required
                >

                <label for="password">Password</label>

                <input
                    type="password"
                    name="password"
                    id="password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <p class="register-hint">
                    Minimum 8 characters.
                </p>

                <label for="city">City</label>

                <input
                    type="text"
                    name="city"
                    id="city"
                    value="<?php echo htmlspecialchars(
                        $city ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >

                <label for="bio">About You (Optional)</label>

                <textarea
                    name="bio"
                    id="bio"
                    rows="4"
                    maxlength="500"
                    placeholder="Tell us a little about yourself..."
                ><?php echo htmlspecialchars(
                    $bio ?? '',
                    ENT_QUOTES,
                    'UTF-8'
                ); ?></textarea>

                <button type="submit" name="submit">
                    Create Account
                </button>

            </form>

            <p class="register-login">
                Already have an account?
                <a href="login.php">Log In</a>
            </p>

            <a href="index.php" class="register-back">
                Back to Home
            </a>

        </section>

    </main>

</body>

</html>

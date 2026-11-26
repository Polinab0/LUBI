<?php

// Include session functions
require_once "includes/session.php";

// Check if user is logged in
confirm_logged_in();

// Include database and User class
require_once "dbcon.php";
require_once "classes/User.php";

// Connect to database
$database = new Database();
$dbCon = $database->connect();

// Create User object
$userObject = new User($dbCon);

// Get logged in user
$userID = $_SESSION['userID'];

// Get current user information
$currentUser = $userObject->getUserById($userID);

// Store validation errors
$errors = [];

// Handle form submission
if (isset($_POST['submit'])) {

    // Check CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // Get form values
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid.";
    }

    // Validate bio length
    if (mb_strlen($bio, 'UTF-8') > 500) {
        $errors[] = "Bio cannot be longer than 500 characters.";
    }

    // Update profile if there are no errors
    if (empty($errors)) {

        $userObject->updateProfile(
            $userID,
            $username,
            $email,
            $city,
            $bio
        );

        // Update username in session
        $_SESSION['username'] = $username;

        // Redirect to profile
        header("Location: profile.php");
        exit();
    }
}

// Escape text safely
function editProfileText($value) {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LUBI — Edit Profile</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        .edit-profile-page {
            max-width: 850px;
            margin: 70px auto 110px;
            padding: 0 24px;
        }

        .edit-profile-back {
            display: inline-block;
            margin-bottom: 35px;
            color: #51564b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .edit-profile-back:hover {
            text-decoration: underline;
        }

        .edit-profile-heading {
            margin-bottom: 35px;
        }

        .edit-profile-heading h1 {
            font-size: clamp(38px, 6vw, 60px);
            line-height: 1.15;
            margin: 12px 0;
            color: #22251f;
        }

        .edit-profile-heading h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .edit-profile-heading p {
            color: #777a70;
            font-size: 15px;
            line-height: 1.7;
        }

        .edit-profile-card {
            background: #ffffff;
            border: 1px solid #e5e6df;
            padding: 40px;
        }

        .edit-profile-card h2 {
            margin: 0 0 30px;
            font-size: 24px;
            color: #252820;
        }

        .edit-profile-field {
            margin-bottom: 24px;
        }

        .edit-profile-field label {
            display: block;
            margin-bottom: 9px;
            color: #343a30;
            font-size: 13px;
            font-weight: 700;
        }

        .edit-profile-field input,
        .edit-profile-field textarea {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 14px 16px;
            background: #fafbf8;
            border: 1px solid #d9ded2;
            border-radius: 0;
            color: #252820;
            font: inherit;
            font-size: 14px;
        }

        .edit-profile-field input:focus,
        .edit-profile-field textarea:focus {
            outline: 2px solid #a6b795;
            outline-offset: 1px;
        }

        .edit-profile-field textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.7;
        }

        .edit-profile-hint {
            margin-top: 8px;
            font-size: 12px;
            color: #85897f;
        }

        .edit-profile-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            padding-top: 25px;
            border-top: 1px solid #e8e9e3;
        }

        .edit-profile-save {
            padding: 14px 26px;
            background: #242720;
            border: 1px solid #242720;
            color: #ffffff;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .edit-profile-save:hover {
            background: #41483a;
        }

        .edit-profile-cancel {
            display: inline-block;
            padding: 14px 26px;
            background: #e9eddf;
            border: 1px solid #d6dbcb;
            color: #242820;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .edit-profile-cancel:hover {
            background: #d8e1ca;
        }

        .edit-profile-errors {
            background: #f9eeee;
            border-left: 4px solid #aa4c4c;
            padding: 18px 22px;
            margin-bottom: 25px;
        }

        .edit-profile-errors p {
            color: #943c3c;
            font-size: 14px;
            margin: 6px 0;
        }

        @media (max-width: 600px) {
            .edit-profile-page {
                margin-top: 40px;
                padding: 0 16px;
            }

            .edit-profile-card {
                padding: 24px;
            }

            .edit-profile-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .edit-profile-save,
            .edit-profile-cancel {
                text-align: center;
            }
        }
    </style>

</head>

<body>

    <!-- LUBI Header -->
    <header class="header">

        <a href="index.php" class="wordmark">
            LUBI<span class="wordmark-dot">.</span>
        </a>

        <nav class="main-nav">
            <a href="index.php">Home</a>
            <a href="saved.php">Saved Posts</a>
            <a href="profile.php">My Profile</a>

            <?php if (
                isset($_SESSION['userType']) &&
                $_SESSION['userType'] === 'admin'
            ): ?>
                <a href="admin.php">Admin Panel</a>
            <?php endif; ?>
        </nav>

        <div class="header-right">
            <a href="logout.php" class="button-dark">
                Logout
            </a>
        </div>

    </header>

    <main class="edit-profile-page">

        <a href="profile.php" class="edit-profile-back">
            ← Back to Profile
        </a>

        <!-- Page heading -->
        <div class="edit-profile-heading">

            <span class="eyebrow">
                LUBI / YOUR ACCOUNT
            </span>

            <h1>
                Edit <em>Profile.</em>
            </h1>

            <p>
                Update your personal information and how you appear
                in the LUBI community.
            </p>

        </div>

        <!-- Profile form -->
        <section class="edit-profile-card">

            <h2>Personal Information</h2>

            <!-- Validation errors -->
            <?php if (!empty($errors)): ?>

                <div class="edit-profile-errors" role="alert">

                    <?php foreach ($errors as $error): ?>

                        <p>
                            <?php echo editProfileText($error); ?>
                        </p>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <form action="editProfile.php" method="post">

                <!-- CSRF protection -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo editProfileText(csrf_token()); ?>"
                >

                <!-- Username -->
                <div class="edit-profile-field">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        id="username"
                        value="<?php echo editProfileText($username ?? $currentUser['username']); ?>"
                        autocomplete="username"
                        required
                    >

                </div>

                <!-- Email -->
                <div class="edit-profile-field">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="<?php echo editProfileText($email ?? $currentUser['email']); ?>"
                        autocomplete="email"
                        required
                    >

                </div>

                <!-- City -->
                <div class="edit-profile-field">

                    <label for="city">
                        City
                    </label>

                    <input
                        type="text"
                        name="city"
                        id="city"
                        value="<?php echo editProfileText($city ?? $currentUser['city']); ?>"
                        autocomplete="address-level2"
                    >

                </div>

                <!-- Bio -->
                <div class="edit-profile-field">

                    <label for="bio">
                        About You
                    </label>

                    <textarea
                        name="bio"
                        id="bio"
                        maxlength="500"
                        placeholder="Tell the community a little about yourself..."
                    ><?php echo editProfileText($bio ?? $currentUser['bio']); ?></textarea>

                    <p class="edit-profile-hint">
                        Maximum 500 characters.
                    </p>

                </div>

                <!-- Form buttons -->
                <div class="edit-profile-actions">

                    <button
                        type="submit"
                        name="submit"
                        class="edit-profile-save"
                    >
                        Save Changes
                    </button>

                    <a
                        href="profile.php"
                        class="edit-profile-cancel"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </section>

    </main>

</body>

</html>

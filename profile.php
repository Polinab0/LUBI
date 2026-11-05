<?php

// Include the session functions
require_once "includes/session.php";

// Check if the user is logged in
confirm_logged_in();

// Include the database connection
require_once "dbcon.php";

// Include the User class
require_once "classes/User.php";

// Include the Post class
require_once "classes/Post.php";

// Create the Database object
$database = new Database();

// Create the PDO connection
$dbCon = $database->connect();

// Create objects
$userObject = new User($dbCon);
$postObject = new Post($dbCon);

// Get the logged in user ID
$userID = $_SESSION['userID'];

// Get the user information
$currentUser = $userObject->getUserById($userID);

// Get all posts
$allPosts = $postObject->getAllPosts();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Profile</title>
</head>

<body>

    <a href="index.php">Back to Feed</a>

    <h1>Profile</h1>

    <h2>
        <?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?>
    </h2>

    <p>
        User type:
        <?php echo htmlspecialchars($currentUser['userType'], ENT_QUOTES, 'UTF-8'); ?>
    </p>

    <p>
        Email:
        <?php echo htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8'); ?>
    </p>

    <p>
        City:
        <?php echo htmlspecialchars($currentUser['city'], ENT_QUOTES, 'UTF-8'); ?>
    </p>

    <p>
        Bio:
        <?php echo htmlspecialchars($currentUser['bio'], ENT_QUOTES, 'UTF-8'); ?>
    </p>

    <a href="editProfile.php">
        Edit Profile
    </a>

    <hr>

    <h2>My Posts</h2>

    <?php foreach ($allPosts as $post) { ?>

        <?php if ($post['username'] == $currentUser['username']) { ?>

            <div>

                <p>
                    Category:
                    <?php echo htmlspecialchars($post['categoryName'], ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <?php if (!empty($post['file'])) { ?>

                    <a href="post.php?postID=<?php echo $post['postID']; ?>">

                        <img
                            src="<?php echo htmlspecialchars($post['file'], ENT_QUOTES, 'UTF-8'); ?>"
                            alt="Post image"
                            width="250"
                        >

                    </a>

                <?php } ?>

                <p>
                    <?php echo htmlspecialchars($post['caption'], ENT_QUOTES, 'UTF-8'); ?>
                </p>

            </div>

            <hr>

        <?php } ?>

    <?php } ?>

</body>

</html>
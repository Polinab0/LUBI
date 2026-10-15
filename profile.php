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

// Create a database connection
$dbCon = dbCon($user, $pass);

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
        <?php echo $currentUser['username']; ?>
    </h2>

    <p>
        User type:
        <?php echo $currentUser['userType']; ?>
    </p>

    <p>
        Email:
        <?php echo $currentUser['email']; ?>
    </p>

    <p>
        City:
        <?php echo $currentUser['city']; ?>
    </p>

    <p>
        Bio:
        <?php echo $currentUser['bio']; ?>
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
                    <?php echo $post['categoryName']; ?>
                </p>

                <?php if (!empty($post['file'])) { ?>

                    <a href="post.php?postID=<?php echo $post['postID']; ?>">

                        <img
                            src="<?php echo $post['file']; ?>"
                            alt="Post image"
                            width="250"
                        >

                    </a>

                <?php } ?>

                <p>
                    <?php echo $post['caption']; ?>
                </p>

            </div>

            <hr>

        <?php } ?>

    <?php } ?>

</body>

</html>
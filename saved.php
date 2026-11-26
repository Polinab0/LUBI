<?php

// Show PHP errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the session functions
require_once "includes/session.php";

// Check if the user is logged in
confirm_logged_in();

// Include the database connection
require_once "dbcon.php";

// Create the Database object
$database = new Database();

// Connect to the database
$dbCon = $database->connect();

// Get the logged in user's ID
$userID = $_SESSION['userID'];

// TEMPORARY: Check which user is logged in
echo "Current user ID: " . $userID;

// Get the posts saved by this user
$query = $dbCon->prepare(
    "SELECT post.postID, post.caption, user.username,
            category.name AS categoryName, media.file
     FROM saved_post
     JOIN post ON saved_post.postID = post.postID
     JOIN user ON post.userID = user.userID
     JOIN category ON post.categoryID = category.categoryID
     LEFT JOIN media ON post.postID = media.postID
     WHERE saved_post.userID = :userID"
);

// Bind the logged in user's ID
$query->bindValue(':userID', $userID, PDO::PARAM_INT);

// Run the query
$query->execute();

// Get the saved posts
$savedPosts = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Saved Posts</title>
</head>

<body>

    <a href="index.php">Back to Home</a>

    <h1>Saved Posts</h1>

    <?php if (count($savedPosts) > 0) { ?>

        <?php foreach ($savedPosts as $post) { ?>

            <div>
                <h3>
                    <?php echo htmlspecialchars($post['username']); ?>
                </h3>

                <p>
                    Category:
                    <?php echo htmlspecialchars($post['categoryName']); ?>
                </p>

                <p>
                    <?php echo htmlspecialchars($post['caption']); ?>
                </p>

                <?php if (!empty($post['file'])) { ?>

                    <img
                        src="<?php echo htmlspecialchars($post['file']); ?>"
                        width="250"
                        alt="Saved post image"
                    >

                <?php } ?>

                <p>
                    <a href="post.php?postID=<?php echo $post['postID']; ?>">
                        View Post
                    </a>
                </p>
            </div>

            <hr>

        <?php } ?>

    <?php } else { ?>

        <p>You haven't saved any posts yet.</p>

    <?php } ?>

</body>

</html>

<?php

// Include the session functions
require_once "includes/session.php";

// Check if the user is logged in
confirm_logged_in();

// Include the database connection
require_once "dbcon.php";

// Include the Post class
require_once "classes/Post.php";

// Include the Comment class
require_once "classes/Comment.php";

// Create a database connection
$dbCon = dbCon($user, $pass);

// Create objects
$postObject = new Post($dbCon);
$commentObject = new Comment($dbCon);

// Check if postID exists in the URL
if (isset($_GET['postID'])) {

    // Get the post ID from the URL
    $postID = $_GET['postID'];

    // Get the post from the database
    $post = $postObject->getPostById($postID);

} else {

    // Redirect if no post ID was provided
    header("Location: index.php");
    exit();
}


// Add a like
if (isset($_POST['like'])) {

    // Get the logged in user ID
    $userID = $_SESSION['userID'];

    // Add the like
    $postObject->likePost(
        $userID,
        $postID
    );

    // Reload the post page
    header("Location: post.php?postID=" . $postID);
    exit();
}


// Add a comment
if (isset($_POST['addComment'])) {

    // Get the logged in user ID
    $userID = $_SESSION['userID'];

    // Get the comment text
    $commentText = $_POST['commentText'];

    // Add the comment
    $commentObject->addComment(
        $userID,
        $postID,
        $commentText
    );

    // Reload the post page
    header("Location: post.php?postID=" . $postID);
    exit();
}


// Get like count
$likeCount = $postObject->getLikeCount($postID);

// Get comment count
$commentCount = $commentObject->getCommentCount($postID);

// Get all comments
$comments = $commentObject->getComments($postID);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Post</title>
</head>

<body>

    <a href="index.php">
        Back to Feed
    </a>

    <hr>

    <?php if ($post) { ?>

        <h2>
            <?php echo $post['username']; ?>
        </h2>

        <p>
            Category:
            <?php echo $post['categoryName']; ?>
        </p>

        <?php if (!empty($post['file'])) { ?>

            <img
                src="<?php echo $post['file']; ?>"
                alt="Post image"
                width="500"
            >

        <?php } ?>

        <p>
            <?php echo $post['caption']; ?>
        </p>

        <p>
            Likes:
            <?php echo $likeCount; ?>
        </p>

        <form
            action="post.php?postID=<?php echo $postID; ?>"
            method="post"
        >

            <input
                type="submit"
                name="like"
                value="Like"
            >

        </form>

        <p>
            Comments:
            <?php echo $commentCount; ?>
        </p>

        <form
            action="post.php?postID=<?php echo $postID; ?>"
            method="post"
        >

            <input
                type="text"
                name="commentText"
                placeholder="Write a comment..."
                required
            >

            <input
                type="submit"
                name="addComment"
                value="Comment"
            >

        </form>

        <h3>Comments</h3>

        <?php foreach ($comments as $comment) { ?>

            <p>

                <strong>
                    <?php echo $comment['username']; ?>:
                </strong>

                <?php echo $comment['text']; ?>

            </p>

        <?php } ?>

    <?php } else { ?>

        <p>Post not found.</p>

    <?php } ?>

</body>

</html>
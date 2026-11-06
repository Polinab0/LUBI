
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

// Create the Database object
$database = new Database();

// Create the PDO connection
$dbCon = $database->connect();

// Create objects
$postObject = new Post($dbCon);
$commentObject = new Comment($dbCon);

// Get the logged in user's ID
$userID = $_SESSION['userID'];

// Check if postID exists in the URL
if (isset($_GET['postID'])) {

    // Get the post ID from the URL
    $postID = filter_input(
        INPUT_GET,
        'postID',
        FILTER_VALIDATE_INT
    );

    if (!$postID) {
        header("Location: index.php");
        exit();
    }

    // Get the post from the database
    $post = $postObject->getPostById($postID);

} else {

    // Redirect if no post ID was provided
    header("Location: index.php");
    exit();
}

// Add a like
if (isset($_POST['like'])) {

    // Check the CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // Add the like
    $postObject->likePost(
        $userID,
        $postID
    );

    // Reload the post page
    header("Location: post.php?postID=" . $postID);
    exit();
}

// Delete a post
if (isset($_POST['deletePost'])) {

    // Check the CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // Check if the post exists
    if (!$post) {
        die("Post not found.");
    }

    // Check if the user is allowed to delete the post
    if (
        $post['userID'] != $userID &&
        $_SESSION['userType'] !== 'admin'
    ) {
        die("You are not allowed to delete this post.");
    }

    // Delete the post
    $postObject->deletePost($postID);

    // Redirect back to the feed
    header("Location: index.php");
    exit();
}

// Add a comment
if (isset($_POST['addComment'])) {

    // Check the CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // Get the comment text
    $commentText = trim($_POST['commentText'] ?? '');

    // Check if the comment is empty
    if (empty($commentText)) {
        die("Comment cannot be empty.");
    }

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

// Check if this post is saved by the logged in user
$query = $dbCon->prepare(
    "SELECT postID FROM saved_post
     WHERE userID = :userID
     AND postID = :postID"
);

$query->execute([
    ':userID' => $userID,
    ':postID' => $postID
]);

// True if the current user has saved this post
$isSaved = $query->fetch(PDO::FETCH_ASSOC) !== false;

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
            <strong>
                <?php echo htmlspecialchars(
                    $post['username'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </strong>
        </h2>

        <p>
            Category:
            <?php echo htmlspecialchars(
                $post['categoryName'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </p>

        <?php if (!empty($post['file'])) { ?>

            <img
                src="<?php echo htmlspecialchars(
                    $post['file'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
                alt="Post image"
                width="500"
            >

        <?php } ?>

        <p>
            <?php echo htmlspecialchars(
                $post['caption'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </p>

        <!-- Save or unsave this post -->
        <form action="savePost.php" method="post">

            <input
                type="hidden"
                name="postID"
                value="<?php echo $postID; ?>"
            >

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

            <input
                type="submit"
                value="<?php echo $isSaved ? 'Unsave Post' : 'Save Post'; ?>"
            >

        </form>

        <p>
            Likes:
            <?php echo $likeCount; ?>
        </p>

        <!-- Like the post -->
        <form
            action="post.php?postID=<?php echo $postID; ?>"
            method="post"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars(
                    csrf_token(),
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
            >

            <input
                type="submit"
                name="like"
                value="Like"
            >

        </form>

        <!-- Show delete button to owner or admin -->
        <?php if (
            $post['userID'] == $userID ||
            $_SESSION['userType'] === 'admin'
        ) { ?>

            <form
                action="post.php?postID=<?php echo $postID; ?>"
                method="post"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars(
                        csrf_token(),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >

                <input
                    type="submit"
                    name="deletePost"
                    value="Delete Post"
                >

            </form>

        <?php } ?>

        <p>
            Comments:
            <?php echo $commentCount; ?>
        </p>

        <!-- Add a comment -->
        <form
            action="post.php?postID=<?php echo $postID; ?>"
            method="post"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars(
                    csrf_token(),
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
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

        <!-- Display comments -->
        <?php foreach ($comments as $comment) { ?>

            <p>
                <strong>
                    <?php echo htmlspecialchars(
                        $comment['username'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </strong>

                <?php echo htmlspecialchars(
                    $comment['text'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </p>

        <?php } ?>

    <?php } else { ?>

        <p>Post not found.</p>

    <?php } ?>

</body>

</html>

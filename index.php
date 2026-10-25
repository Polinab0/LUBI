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

// Create a Post object
$postObject = new Post($dbCon);

// Create a Comment object
$commentObject = new Comment($dbCon);


// Check if the like button was pressed
if (isset($_POST['like'])) {

    // Get the post ID
    $postID = $_POST['postID'];

    // Get the logged in user ID
    $userID = $_SESSION['userID'];

    // Add the like
    $postObject->likePost(
        $userID,
        $postID
    );

    // Reload the page
    header("Location: index.php");
    exit();
}


// Check if a comment was submitted
if (isset($_POST['addComment'])) {

    // Get the post ID
    $postID = $_POST['postID'];

    // Get the comment text
    $commentText = $_POST['commentText'];

    // Get the logged in user ID
    $userID = $_SESSION['userID'];

    // Add the comment
    $commentObject->addComment(
        $userID,
        $postID,
        $commentText
    );

    // Reload the page
    header("Location: index.php");
    exit();
}


// Get all posts from the database
$posts = $postObject->getAllPosts();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Feed</title>
</head>

<body>

    <h1>Welcome to LUBI</h1>

    <h2>
        Hello <?php echo $_SESSION['username']; ?>
    </h2>

    <p>
        User type:
        <?php echo $_SESSION['userType']; ?>
    </p>

    <a href="createPost.php">
        Create Post
    </a>

    <a href="profile.php">My Profile</a>

    <br><br>

    <a href="logout.php">
        Logout
    </a>

    <hr>

    <h2>Latest Posts</h2>


    <?php foreach ($posts as $post) { ?>

        <div>

            <!-- Display username -->
            <h3>
                <?php echo $post['username']; ?>
            </h3>


            <!-- Display category -->
            <p>
                Category:
                <?php echo $post['categoryName']; ?>
            </p>


            <!-- Display post image -->
            <?php if (!empty($post['file'])) { ?>

                <a href="post.php?postID=<?php echo $post['postID']; ?>">

                    <img
                        src="<?php echo $post['file']; ?>"
                        alt="Post image"
                        width="300"
                    >

                </a>

            <?php } ?>


            <!-- Display caption -->
            <p>
                <?php echo $post['caption']; ?>
            </p>


            <!-- Display post date -->
            <small>
                <?php echo $post['createdAt']; ?>
            </small>


            <?php

            // Get the number of likes
            $likeCount = $postObject->getLikeCount(
                $post['postID']
            );

            // Get the number of comments
            $commentCount = $commentObject->getCommentCount(
                $post['postID']
            );

            ?>


            <!-- Display number of likes -->
            <p>
                Likes:
                <?php echo $likeCount; ?>
            </p>


            <!-- Like form -->
            <form action="index.php" method="post">

                <input
                    type="hidden"
                    name="postID"
                    value="<?php echo $post['postID']; ?>"
                >

                <input
                    type="submit"
                    name="like"
                    value="Like"
                >

            </form>


            <!-- Display number of comments -->
            <p>
                Comments:
                <?php echo $commentCount; ?>
            </p>


            <!-- Add comment form -->
            <form action="index.php" method="post">

                <input
                    type="hidden"
                    name="postID"
                    value="<?php echo $post['postID']; ?>"
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


            <?php

            // Get all comments for this post
            $comments = $commentObject->getComments(
                $post['postID']
            );

            ?>


            <!-- Display comments -->
            <?php foreach ($comments as $comment) { ?>

                <p>

                    <strong>
                        <?php echo $comment['username']; ?>:
                    </strong>

                    <?php echo $comment['text']; ?>

                </p>

            <?php } ?>

        </div>

        <hr>

    <?php } ?>

</body>

</html>
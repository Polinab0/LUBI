
<?php

// Include session functions
require_once "includes/session.php";

// Only logged in users can save posts
confirm_logged_in();

// Include database connection
require_once "dbcon.php";

// Connect to the database
$database = new Database();
$dbCon = $database->connect();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Check CSRF token
if (
    !isset($_POST['csrf_token']) ||
    !verify_csrf_token($_POST['csrf_token'])
) {
    die("Invalid CSRF token.");
}

// Get the logged in user's ID
$userID = $_SESSION['userID'];

// Get and validate the post ID
$postID = filter_input(INPUT_POST, 'postID', FILTER_VALIDATE_INT);

if (!$postID) {
    die("Invalid post ID.");
}

// Check if the post exists
$query = $dbCon->prepare(
    "SELECT postID FROM post WHERE postID = :postID"
);

$query->execute([
    ':postID' => $postID
]);

if (!$query->fetch()) {
    die("Post not found.");
}

// Check if the user already saved this post
$query = $dbCon->prepare(
    "SELECT postID FROM saved_post
     WHERE userID = :userID AND postID = :postID"
);

$query->execute([
    ':userID' => $userID,
    ':postID' => $postID
]);

$savedPost = $query->fetch(PDO::FETCH_ASSOC);

if ($savedPost) {

    // If already saved, unsave the post
    $query = $dbCon->prepare(
        "DELETE FROM saved_post
         WHERE userID = :userID AND postID = :postID"
    );

} else {

    // Otherwise save the post
    // INSERT IGNORE prevents duplicate entries
    $query = $dbCon->prepare(
        "INSERT IGNORE INTO saved_post (userID, postID)
         VALUES (:userID, :postID)"
    );
}

// Run the save or unsave query
$query->execute([
    ':userID' => $userID,
    ':postID' => $postID
]);

// Redirect to Saved Posts page
header("Location: saved.php");
exit();

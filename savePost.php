<?php

// Include the session functions
require_once "includes/session.php";

// Check if the user is logged in
confirm_logged_in();

// Include the database connection
require_once "dbcon.php";

// Create the database connection
$database = new Database();
$dbCon = $database->connect();

// Get the logged in user's ID
$userID = $_SESSION['userID'];

// Check if a post ID was sent
if (isset($_POST['postID'])) {

    // Get the post ID
    $postID = $_POST['postID'];

    // Check if the post is already saved
    $query = $dbCon->prepare(
        "SELECT * FROM saved_post
         WHERE userID = :userID AND postID = :postID"
    );

    $query->execute([
        ':userID' => $userID,
        ':postID' => $postID
    ]);

    $savedPost = $query->fetch(PDO::FETCH_ASSOC);

    if ($savedPost) {

        // Remove the post from saved posts
        $query = $dbCon->prepare(
            "DELETE FROM saved_post
             WHERE userID = :userID AND postID = :postID"
        );

    } else {

        // Save the post
        $query = $dbCon->prepare(
            "INSERT INTO saved_post (userID, postID)
             VALUES (:userID, :postID)"
        );
    }

    // Execute the query
    $query->execute([
        ':userID' => $userID,
        ':postID' => $postID
    ]);
}

// Go back to the Saved page
header("Location: saved.php");
exit();

?>
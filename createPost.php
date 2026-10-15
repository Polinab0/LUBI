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

// Include the Post class
require_once "classes/Post.php";

// Create a database connection
$dbCon = dbCon($user, $pass);

// Create a Post object
$postObject = new Post($dbCon);

// Get all categories from the database
$categoryQuery = $dbCon->prepare(
    "SELECT * FROM category"
);

// Execute the query
$categoryQuery->execute();

// Get all categories
$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Check if the form was submitted
if (isset($_POST['submit'])) {

    // Get form values
    $categoryID = $_POST['categoryID'];
    $caption = $_POST['caption'];

    // Get the logged in user ID from the session
    $userID = $_SESSION['userID'];

    // Create the new post
    $postID = $postObject->createPost(
        $userID,
        $categoryID,
        $caption
    );

    // Check if an image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

        // Get the uploaded file information
        $fileName = $_FILES['image']['name'];
        $tempName = $_FILES['image']['tmp_name'];
        $fileType = $_FILES['image']['type'];

        // Create a unique file name
        $newFileName = time() . "_" . $fileName;

        // Create the upload path
        $uploadPath = "uploads/" . $newFileName;

        // Move the uploaded file to the uploads folder
        if (move_uploaded_file($tempName, $uploadPath)) {

            // Save the media information in the database
            $postObject->addMedia(
                $postID,
                $uploadPath,
                $fileType
            );

            $message = "Post and image uploaded successfully!";

        } else {

            $message = "Post created, but image upload failed.";
        }

    } else {

        $message = "Post created, but no image was uploaded.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Create Post</title>
</head>

<body>

    <h1>Create Post</h1>

    <?php
    if (isset($message)) {
        echo "<p>" . $message . "</p>";
    }
    ?>

    <form
        action="createPost.php"
        method="post"
        enctype="multipart/form-data"
    >

        <label for="categoryID">Category:</label>

        <select name="categoryID" id="categoryID" required>

            <?php foreach ($categories as $category) { ?>

                <option value="<?php echo $category['categoryID']; ?>">
                    <?php echo $category['name']; ?>
                </option>

            <?php } ?>

        </select>

        <br><br>

        <label for="caption">Caption:</label>

        <textarea
            name="caption"
            id="caption"
            required
        ></textarea>

        <br><br>

        <label for="image">Choose Image:</label>

        <input
            type="file"
            name="image"
            id="image"
            accept="image/*"
            required
        >

        <br><br>

        <input
            type="submit"
            name="submit"
            value="Create Post"
        >

    </form>

    <br>

    <a href="index.php">Back to Home</a>

</body>

</html>
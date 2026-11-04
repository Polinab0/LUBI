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

// Create the Database object
$database = new Database();

// Create the PDO connection
$dbCon = $database->connect();

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

    // Check the CSRF token
    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // Get form values
    $categoryID = filter_input(
        INPUT_POST,
        'categoryID',
        FILTER_VALIDATE_INT
    );
    $caption = trim($_POST['caption']);
    // Check if category ID is valid
if ($categoryID === false || $categoryID === null) {
    die("Invalid category.");
}

    // Check if caption is empty
    if (empty($caption)) {
        die("Caption is required.");
    }
    // Get the logged in user ID from the session
    $userID = $_SESSION['userID'];

    try {

    // Start the transaction
    $dbCon->beginTransaction();

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

    // Get the uploaded file size
    $fileSize = $_FILES['image']['size'];

    // Maximum file size: 5 MB
    $maxFileSize = 5 * 1024 * 1024;

    // Check file size
    if ($fileSize > $maxFileSize) {
        throw new Exception("Image file is too large.");
    }

    // Check the real MIME type of the file
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $realFileType = finfo_file($fileInfo, $tempName);
    finfo_close($fileInfo);

    // Allowed image types
    $allowedTypes = [
     'image/jpeg' => 'jpg',
     'image/png' => 'png',
     'image/webp' => 'webp'
    ];

    // Check file type
    if (!isset($allowedTypes[$realFileType]))  {
        throw new Exception("Only JPEG, PNG and WebP images are allowed.");
    }

    // Get image dimensions
    $imageInfo = getimagesize($tempName);

    // Check if the uploaded file is a valid image
    if ($imageInfo === false) {
        throw new Exception("Uploaded file is not a valid image.");
    }

    $imageWidth = $imageInfo[0];
    $imageHeight = $imageInfo[1];

    // Maximum image dimensions
    $maxWidth = 2000;
    $maxHeight = 2000;

    // Check image dimensions
    if ($imageWidth > $maxWidth || $imageHeight > $maxHeight) {
        throw new Exception("Image dimensions are too large.");
    }


        

        // Create a unique file name
        $newFileName = bin2hex(random_bytes(16)) . "." . $allowedTypes[$realFileType];

        // Create the upload path
        $uploadPath = "uploads/" . $newFileName;

        // Move the uploaded file to the uploads folder
        if (move_uploaded_file($tempName, $uploadPath)) {

            // Save the media information in the database
            $postObject->addMedia(
                $postID,
                $uploadPath,
                $realFileType
            );

            // Save the transaction
            $dbCon->commit();

            $message = "Post and image uploaded successfully!";

        } else {

            throw new Exception("Image upload failed.");
        }

    } else {

        throw new Exception("No image was uploaded.");
    }

    } catch (Exception $error) {

    // Undo database changes if something failed
    if ($dbCon->inTransaction()) {
        $dbCon->rollBack();
    }

    $message = $error->getMessage();
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
       echo "<p>" . htmlspecialchars(
         $message,
         ENT_QUOTES,
         'UTF-8'
       ) . "</p>";
    }
    ?>

    <form
        action="createPost.php"
        method="post"
        enctype="multipart/form-data"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?php echo csrf_token(); ?>"
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

<?php

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

    // Prevent post spam
    if (
        isset($_SESSION['last_post_time']) &&
        time() - $_SESSION['last_post_time'] < 3
    ) {
        die("Please wait a few seconds before creating another post.");
    }

    // Check the CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
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
        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] == 0
        ) {

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
            if (!isset($allowedTypes[$realFileType])) {
                throw new Exception(
                    "Only JPEG, PNG and WebP images are allowed."
                );
            }

            // Get image dimensions
            $imageInfo = getimagesize($tempName);

            // Check if the uploaded file is a valid image
            if ($imageInfo === false) {
                throw new Exception(
                    "Uploaded file is not a valid image."
                );
            }

            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];

            // Maximum image dimensions
            $maxWidth = 4200;
            $maxHeight = 4200;

            // Check image dimensions
            if (
                $imageWidth > $maxWidth ||
                $imageHeight > $maxHeight
            ) {
                throw new Exception(
                    "Image dimensions are too large."
                );
            }

            // Create a unique file name
            $newFileName = bin2hex(random_bytes(16))
                . "." . $allowedTypes[$realFileType];

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

                // Save the time of the last created post
                $_SESSION['last_post_time'] = time();

                header("Location: post.php?postID=" . $postID);
                exit();

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

// Escape output safely
function createPostText($value) {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LUBI — Create Post</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        .create-post-page {
            max-width: 850px;
            margin: 70px auto 110px;
            padding: 0 24px;
        }

        .create-post-back {
            display: inline-block;
            margin-bottom: 35px;
            color: #51564b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .create-post-back:hover {
            text-decoration: underline;
        }

        .create-post-heading {
            margin-bottom: 35px;
        }

        .create-post-heading h1 {
            font-size: clamp(38px, 6vw, 60px);
            line-height: 1.15;
            margin: 12px 0;
            color: #22251f;
        }

        .create-post-heading h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .create-post-heading p {
            color: #777a70;
            font-size: 15px;
            line-height: 1.7;
        }

        .create-post-card {
            background: #ffffff;
            border: 1px solid #e5e6df;
            padding: 40px;
        }

        .create-post-card h2 {
            margin: 0 0 30px;
            font-size: 24px;
            color: #252820;
        }

        .create-post-field {
            margin-bottom: 24px;
        }

        .create-post-field label {
            display: block;
            margin-bottom: 9px;
            color: #343a30;
            font-size: 13px;
            font-weight: 700;
        }

        .create-post-field select,
        .create-post-field textarea,
        .create-post-field input[type="file"] {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 14px 16px;
            background: #fafbf8;
            border: 1px solid #d9ded2;
            border-radius: 0;
            color: #252820;
            font: inherit;
            font-size: 14px;
        }

        .create-post-field select:focus,
        .create-post-field textarea:focus,
        .create-post-field input:focus {
            outline: 2px solid #a6b795;
            outline-offset: 1px;
        }

        .create-post-field textarea {
            min-height: 160px;
            resize: vertical;
            line-height: 1.7;
        }

        .create-post-field input[type="file"] {
            cursor: pointer;
        }

        .create-post-field input[type="file"]::file-selector-button {
            background: #e9eddf;
            color: #252820;
            border: 1px solid #d6dbcb;
            padding: 10px 15px;
            margin-right: 15px;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .create-post-hint {
            margin-top: 8px;
            font-size: 12px;
            color: #85897f;
            line-height: 1.6;
        }

        .create-post-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            padding-top: 25px;
            border-top: 1px solid #e8e9e3;
        }

        .create-post-submit {
            padding: 14px 26px;
            background: #242720;
            border: 1px solid #242720;
            color: #ffffff;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .create-post-submit:hover {
            background: #41483a;
        }

        .create-post-cancel {
            display: inline-block;
            padding: 14px 26px;
            background: #e9eddf;
            border: 1px solid #d6dbcb;
            color: #242820;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .create-post-cancel:hover {
            background: #d8e1ca;
        }

        .create-post-error {
            background: #f9eeee;
            border-left: 4px solid #aa4c4c;
            padding: 18px 22px;
            margin-bottom: 25px;
            color: #943c3c;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .create-post-page {
                margin-top: 40px;
                padding: 0 16px;
            }

            .create-post-card {
                padding: 24px;
            }

            .create-post-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .create-post-submit,
            .create-post-cancel {
                text-align: center;
            }
        }
    </style>

</head>

<body>

    <!-- LUBI Header -->
    <header class="header">

        <a href="index.php" class="wordmark">
            LUBI<span class="wordmark-dot">.</span>
        </a>

        <nav class="main-nav">

            <a href="index.php">Home</a>
            <a href="saved.php">Saved Posts</a>
            <a href="profile.php">My Profile</a>

            <?php if (
                isset($_SESSION['userType']) &&
                $_SESSION['userType'] === 'admin'
            ): ?>

                <a href="admin.php">Admin Panel</a>

            <?php endif; ?>

        </nav>

        <div class="header-right">

            <a href="logout.php" class="button-dark">
                Logout
            </a>

        </div>

    </header>

    <main class="create-post-page">

        <a href="index.php" class="create-post-back">
            ← Back to Home
        </a>

        <!-- Page heading -->
        <div class="create-post-heading">

            <span class="eyebrow">
                LUBI / COMMUNITY
            </span>

            <h1>
                Create <em>Post.</em>
            </h1>

            <p>
                Share your beauty inspiration, ideas and creativity
                with the LUBI community.
            </p>

        </div>

        <!-- Create Post form -->
        <section class="create-post-card">

            <h2>Share Something New</h2>

            <!-- Display error message -->
            <?php if (isset($message)): ?>

                <div class="create-post-error" role="alert">
                    <?php echo createPostText($message); ?>
                </div>

            <?php endif; ?>

            <form
                action="createPost.php"
                method="post"
                enctype="multipart/form-data"
            >

                <!-- CSRF protection -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo createPostText(csrf_token()); ?>"
                >

                <!-- Category -->
                <div class="create-post-field">

                    <label for="categoryID">
                        Category
                    </label>

                    <select
                        name="categoryID"
                        id="categoryID"
                        required
                    >

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?php echo (int) $category['categoryID']; ?>"
                            >
                                <?php echo createPostText($category['name']); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Caption -->
                <div class="create-post-field">

                    <label for="caption">
                        Caption
                    </label>

                    <textarea
                        name="caption"
                        id="caption"
                        placeholder="Tell the community about your post..."
                        required
                    ><?php echo createPostText($_POST['caption'] ?? ''); ?></textarea>

                </div>

                <!-- Image upload -->
                <div class="create-post-field">

                    <label for="image">
                        Upload Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        id="image"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >

                    <p class="create-post-hint">
                        Supported formats: JPG, PNG and WebP.
                        Maximum file size: 5 MB.
                    </p>

                </div>

                <!-- Buttons -->
                <div class="create-post-actions">

                    <button
                        type="submit"
                        name="submit"
                        class="create-post-submit"
                    >
                        Create Post
                    </button>

                    <a
                        href="index.php"
                        class="create-post-cancel"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </section>

    </main>

</body>

</html>


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

// Create the Database object
$database = new Database();

// Create the PDO connection
$dbCon = $database->connect();

// Create objects
$userObject = new User($dbCon);
$postObject = new Post($dbCon);

// Get the logged in user ID
$userID = $_SESSION['userID'];

// Get the user information
$currentUser = $userObject->getUserById($userID);

// Get all posts
$allPosts = $postObject->getAllPosts();

// Get only this user's posts
$myPosts = [];

foreach ($allPosts as $post) {
    if ((int)$post['userID'] === (int)$userID) {
        $myPosts[$post['postID']] = $post;
    }
}

// Safely display text
function profileText($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
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

    <title>LUBI - My Profile</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="profile-page">

    <main class="profile-container">

        <header class="profile-header">

            <a href="index.php" class="profile-logo">
                LUBI.
            </a>

            <a href="index.php" class="profile-back">
                Back to Feed
            </a>

        </header>

        <section class="profile-intro">

            <p class="profile-eyebrow">
                MY LUBI SPACE
            </p>

            <h1>
                My <span>Profile.</span>
            </h1>

            <p>
                Your beauty journey, your community, your creativity.
            </p>

        </section>

        <section class="profile-card">

            <div class="profile-card-top">

                <div class="profile-avatar">
                    <?php
                    echo profileText(
                        strtoupper(substr($currentUser['username'], 0, 1))
                    );
                    ?>
                </div>

                <div>
                    <h2>
                        <?php echo profileText($currentUser['username']); ?>
                    </h2>

                    <span class="profile-role">
                        <?php echo profileText(ucfirst($currentUser['userType'])); ?>
                    </span>
                </div>

            </div>

            <div class="profile-details">

                <div class="profile-detail">
                    <span>Email Address</span>
                    <p>
                        <?php echo profileText($currentUser['email']); ?>
                    </p>
                </div>

                <div class="profile-detail">
                    <span>City</span>
                    <p>
                        <?php echo profileText($currentUser['city'] ?: 'Not added yet'); ?>
                    </p>
                </div>

                <div class="profile-detail profile-bio">
                    <span>About Me</span>
                    <p>
                        <?php echo nl2br(profileText(
                            $currentUser['bio'] ?: 'No bio added yet.'
                        )); ?>
                    </p>
                </div>

            </div>

            <div class="profile-actions">

                <a href="editProfile.php" class="profile-button">
                    Edit Profile
                </a>

                <a href="myCourses.php" class="profile-button-secondary">
                    My Courses
                </a>

            </div>

        </section>

        <section class="profile-posts-section">

            <div class="profile-posts-heading">

                <div>
                    <p class="profile-eyebrow">
                        MY CREATIONS
                    </p>

                    <h2>
                        My <span>Posts.</span>
                    </h2>
                </div>

                <span class="profile-post-count">
                    <?php echo count($myPosts); ?> Posts
                </span>

            </div>

            <?php if (empty($myPosts)): ?>

                <div class="profile-empty">

                    <h3>No posts yet.</h3>

                    <p>
                        Share your first beauty creation with the community.
                    </p>

                    <a href="createPost.php" class="profile-button">
                        Create a Post
                    </a>

                </div>

            <?php else: ?>

                <div class="profile-post-grid">

                    <?php foreach ($myPosts as $post): ?>

                        <article class="profile-post-card">

                            <a href="post.php?postID=<?php echo (int)$post['postID']; ?>">

                                <?php if (!empty($post['file'])): ?>

                                    <img
                                        src="<?php echo profileText($post['file']); ?>"
                                        alt="Post image"
                                        class="profile-post-image"
                                    >

                                <?php else: ?>

                                    <div class="profile-post-placeholder">
                                        LUBI.
                                    </div>

                                <?php endif; ?>

                            </a>

                            <div class="profile-post-content">

                                <span class="profile-post-category">
                                    <?php echo profileText($post['categoryName']); ?>
                                </span>

                                <p>
                                    <?php echo profileText($post['caption']); ?>
                                </p>

                                <a href="post.php?postID=<?php echo (int)$post['postID']; ?>"
                                   class="profile-view-post">
                                    View Post
                                </a>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>

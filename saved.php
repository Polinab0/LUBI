
<?php

// Include session functions
require_once "includes/session.php";

// Check if user is logged in
confirm_logged_in();

// Include database connection
require_once "dbcon.php";

// Connect to database
$database = new Database();
$dbCon = $database->connect();

// Get logged in user's ID
$userID = $_SESSION['userID'];

// Get only posts saved by this user
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

// Bind user ID safely
$query->bindValue(':userID', $userID, PDO::PARAM_INT);

// Run query
$query->execute();

// Get saved posts
$savedPosts = $query->fetchAll(PDO::FETCH_ASSOC);

// Escape output safely
function savedText($value) {
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

    <title>LUBI — Saved Posts</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        .saved-page {
            max-width: 1100px;
            margin: 70px auto 110px;
            padding: 0 24px;
        }

        .saved-back {
            display: inline-block;
            margin-bottom: 35px;
            color: #51564b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .saved-back:hover {
            text-decoration: underline;
        }

        .saved-heading {
            margin-bottom: 40px;
        }

        .saved-heading h1 {
            font-size: clamp(38px, 6vw, 64px);
            line-height: 1.15;
            margin: 12px 0;
            color: #22251f;
        }

        .saved-heading h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .saved-heading p {
            color: #777a70;
            font-size: 15px;
        }

        .saved-count {
            display: inline-block;
            background: #e9eddf;
            color: #444c39;
            padding: 9px 16px;
            margin-top: 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .saved-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }

        .saved-card {
            background: #ffffff;
            border: 1px solid #e5e6df;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .saved-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .saved-card-top strong {
            font-size: 18px;
            color: #252720;
        }

        .saved-category {
            background: #e9eddf;
            color: #444c39;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
        }

        .saved-card-image {
            width: 100%;
            height: 260px;
            object-fit: contain;
            background: #f1f1eb;
        }

        .saved-card-caption {
            color: #33362f;
            font-size: 16px;
            line-height: 1.7;
            margin: 0;
            overflow-wrap: anywhere;
            flex-grow: 1;
        }

        .saved-card-actions {
            padding-top: 18px;
            border-top: 1px solid #e8e9e3;
        }

        .saved-view-button {
            display: inline-block;
            padding: 12px 22px;
            background: #242720;
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: background 0.2s;
        }

        .saved-view-button:hover {
            background: #41483a;
        }

        .saved-empty {
            background: #e9ebe2;
            padding: 55px 35px;
            text-align: center;
        }

        .saved-empty h2 {
            font-size: 28px;
            margin-bottom: 12px;
        }

        .saved-empty p {
            color: #73786d;
            margin-bottom: 24px;
        }

        @media (max-width: 700px) {
            .saved-grid {
                grid-template-columns: 1fr;
            }

            .saved-page {
                margin-top: 40px;
                padding: 0 16px;
            }

            .saved-card {
                padding: 22px;
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

    <main class="saved-page">

        <a href="index.php" class="saved-back">
            ← Back to Home
        </a>

        <!-- Heading -->
        <div class="saved-heading">

            <span class="eyebrow">
                LUBI / YOUR COLLECTION
            </span>

            <h1>
                Saved <em>Posts.</em>
            </h1>

            <p>
                Your favourite beauty ideas and inspiration, all in one place.
            </p>

            <span class="saved-count">
                <?php echo count($savedPosts); ?>
                Saved <?php echo count($savedPosts) === 1 ? 'Post' : 'Posts'; ?>
            </span>

        </div>

        <!-- Saved Posts -->
        <?php if (!empty($savedPosts)): ?>

            <div class="saved-grid">

                <?php foreach ($savedPosts as $post): ?>

                    <article class="saved-card">

                        <div class="saved-card-top">

                            <strong>
                                <?php echo savedText($post['username']); ?>
                            </strong>

                            <span class="saved-category">
                                <?php echo savedText($post['categoryName']); ?>
                            </span>

                        </div>

                        <?php if (!empty($post['file'])): ?>

                            <img
                                src="<?php echo savedText($post['file']); ?>"
                                alt="Saved post image"
                                class="saved-card-image"
                            >

                        <?php endif; ?>

                        <p class="saved-card-caption">
                            <?php echo nl2br(savedText($post['caption'])); ?>
                        </p>

                        <div class="saved-card-actions">

                            <a
                                href="post.php?postID=<?php echo (int) $post['postID']; ?>"
                                class="saved-view-button"
                            >
                                View Post
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="saved-empty">

                <h2>No saved posts yet.</h2>

                <p>
                    Explore the community and save posts that inspire you.
                </p>

                <a href="index.php" class="saved-view-button">
                    Explore Posts
                </a>

            </div>

        <?php endif; ?>

    </main>

</body>

</html>

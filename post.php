
<?php

// Include session functions
require_once "includes/session.php";

// Check if user is logged in
confirm_logged_in();

// Include database and classes
require_once "dbcon.php";
require_once "classes/Post.php";
require_once "classes/Comment.php";

// Connect to database
$database = new Database();
$dbCon = $database->connect();

// Create objects
$postObject = new Post($dbCon);
$commentObject = new Comment($dbCon);

// Get logged in user
$userID = $_SESSION['userID'];

// Get post ID
$postID = filter_input(
    INPUT_GET,
    'postID',
    FILTER_VALIDATE_INT
);

if (!$postID) {
    header("Location: index.php");
    exit();
}

// Get post
$post = $postObject->getPostById($postID);

// Handle like
if (isset($_POST['like'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    $postObject->likePost($userID, $postID);

    header("Location: post.php?postID=" . $postID);
    exit();
}

// Handle delete
if (isset($_POST['deletePost'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    if (!$post) {
        die("Post not found.");
    }

    if (
        $post['userID'] != $userID &&
        $_SESSION['userType'] !== 'admin'
    ) {
        die("You are not allowed to delete this post.");
    }

    $postObject->deletePost($postID);

    header("Location: index.php");
    exit();
}

// Handle comment
if (isset($_POST['addComment'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    if (!$post) {
        die("Post not found.");
    }

    $commentText = trim($_POST['commentText'] ?? '');

    if (empty($commentText)) {
        die("Comment cannot be empty.");
    }

    $commentObject->addComment(
        $userID,
        $postID,
        $commentText
    );

    header("Location: post.php?postID=" . $postID);
    exit();
}

// Check if post is saved
$query = $dbCon->prepare(
    "SELECT postID FROM saved_post
     WHERE userID = :userID
     AND postID = :postID"
);

$query->execute([
    ':userID' => $userID,
    ':postID' => $postID
]);

$isSaved = $query->fetch(PDO::FETCH_ASSOC) !== false;

// Get counts and comments
$likeCount = $postObject->getLikeCount($postID);
$commentCount = $commentObject->getCommentCount($postID);
$comments = $commentObject->getComments($postID);

// Escape text safely
function postText($value) {
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

    <title>LUBI — Post</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        .post-detail-page {
            max-width: 900px;
            margin: 70px auto 110px;
            padding: 0 24px;
        }

        .post-detail-back {
            display: inline-block;
            margin-bottom: 35px;
            color: #51564b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .post-detail-back:hover {
            text-decoration: underline;
        }

        .post-detail-heading {
            margin-bottom: 32px;
        }

        .post-detail-heading h1 {
            font-size: clamp(36px, 6vw, 58px);
            line-height: 1.15;
            margin: 12px 0;
            color: #22251f;
        }

        .post-detail-heading h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .post-detail-heading p {
            color: #777a70;
            font-size: 15px;
        }

        .post-detail-card {
            background: #ffffff;
            border: 1px solid #e5e6df;
            padding: 35px;
            border-radius: 4px;
        }

        .post-detail-author {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .post-detail-author strong {
            font-size: 20px;
            color: #252720;
        }

        .post-detail-category {
            background: #e9eddf;
            color: #444c39;
            padding: 9px 16px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .post-detail-image {
            display: block;
            width: 100%;
            max-height: 550px;
            object-fit: contain;
            background: #f1f1eb;
            margin-bottom: 25px;
        }

        .post-detail-caption {
            font-size: 17px;
            line-height: 1.8;
            color: #33362f;
            margin-bottom: 28px;
            overflow-wrap: anywhere;
        }

        .post-detail-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid #e8e9e3;
        }

        .post-detail-actions form {
            margin: 0;
        }

        .post-detail-button {
            display: inline-block;
            padding: 12px 20px;
            border: 1px solid #d6dbcb;
            background: #e8eddf;
            color: #242820;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .post-detail-button:hover {
            background: #d8e1ca;
        }

        .post-detail-button-dark {
            background: #242720;
            color: white;
            border-color: #242720;
        }

        .post-detail-button-dark:hover {
            background: #41483a;
        }

        .post-detail-button-danger {
            background: #aa4c4c;
            color: white;
            border-color: #aa4c4c;
        }

        .post-detail-button-danger:hover {
            background: #903939;
        }

        .post-detail-count {
            margin: 0 12px 0 0;
            color: #6d7268;
            font-size: 14px;
        }

        .post-detail-comments {
            margin-top: 35px;
            padding: 35px;
            background: #e9ebe2;
        }

        .post-detail-comments h2 {
            margin: 0 0 10px;
            font-size: 30px;
            color: #252820;
        }

        .post-detail-comments h2 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .post-detail-comments > p {
            color: #73786d;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .post-detail-comment-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .post-detail-comment-form input[type="text"] {
            flex: 1;
            min-width: 0;
            padding: 14px 16px;
            background: white;
            border: 1px solid #d5d9ce;
            border-radius: 0;
            font: inherit;
            font-size: 14px;
        }

        .post-detail-comment-form input[type="text"]:focus {
            outline: 2px solid #a6b795;
            outline-offset: 1px;
        }

        .post-detail-comment {
            background: #fff;
            padding: 20px;
            margin-top: 12px;
        }

        .post-detail-comment strong {
            display: block;
            margin-bottom: 8px;
            color: #262a23;
        }

        .post-detail-comment p {
            margin: 0;
            color: #53594e;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .post-detail-empty {
            color: #767b70;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .post-detail-page {
                margin-top: 40px;
                padding: 0 16px;
            }

            .post-detail-card,
            .post-detail-comments {
                padding: 22px;
            }

            .post-detail-comment-form {
                flex-direction: column;
            }

            .post-detail-comment-form button {
                width: 100%;
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
            <a href="savedPosts.php">Saved Posts</a>

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

    <main class="post-detail-page">

        <a href="index.php" class="post-detail-back">
            ← Back to Feed
        </a>

        <div class="post-detail-heading">
            <span class="eyebrow">LUBI / COMMUNITY</span>

            <h1>
                Community <em>Post.</em>
            </h1>

            <p>
                Discover ideas, share inspiration and connect with others.
            </p>
        </div>

        <?php if ($post): ?>

            <article class="post-detail-card">

                <div class="post-detail-author">

                    <strong>
                        <?php echo postText($post['username']); ?>
                    </strong>

                    <span class="post-detail-category">
                        <?php echo postText($post['categoryName']); ?>
                    </span>

                </div>

                <?php if (!empty($post['file'])): ?>

                    <img
                        src="<?php echo postText($post['file']); ?>"
                        alt="Post image"
                        class="post-detail-image"
                    >

                <?php endif; ?>

                <p class="post-detail-caption">
                    <?php echo nl2br(postText($post['caption'])); ?>
                </p>

                <div class="post-detail-actions">

                    <!-- Save / Unsave -->
                    <form action="savePost.php" method="post">

                        <input
                            type="hidden"
                            name="postID"
                            value="<?php echo (int) $postID; ?>"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo postText(csrf_token()); ?>"
                        >

                        <button
                            type="submit"
                            class="post-detail-button"
                        >
                            <?php echo $isSaved ? 'Unsave Post' : 'Save Post'; ?>
                        </button>

                    </form>

                    <!-- Like -->
                    <form
                        action="post.php?postID=<?php echo (int) $postID; ?>"
                        method="post"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo postText(csrf_token()); ?>"
                        >

                        <button
                            type="submit"
                            name="like"
                            class="post-detail-button post-detail-button-dark"
                        >
                            Like · <?php echo (int) $likeCount; ?>
                        </button>

                    </form>

                    <!-- Delete -->
                    <?php if (
                        $post['userID'] == $userID ||
                        $_SESSION['userType'] === 'admin'
                    ): ?>

                        <form
                            action="post.php?postID=<?php echo (int) $postID; ?>"
                            method="post"
                            onsubmit="return confirm('Are you sure you want to delete this post?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo postText(csrf_token()); ?>"
                            >

                            <button
                                type="submit"
                                name="deletePost"
                                class="post-detail-button post-detail-button-danger"
                            >
                                Delete Post
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </article>

            <!-- Comments -->
            <section class="post-detail-comments">

                <h2>
                    Comments <em>(<?php echo (int) $commentCount; ?>)</em>
                </h2>

                <p>Join the conversation.</p>

                <form
                    action="post.php?postID=<?php echo (int) $postID; ?>"
                    method="post"
                    class="post-detail-comment-form"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo postText(csrf_token()); ?>"
                    >

                    <input
                        type="text"
                        name="commentText"
                        placeholder="Write a comment..."
                        maxlength="1000"
                        required
                    >

                    <button
                        type="submit"
                        name="addComment"
                        class="post-detail-button post-detail-button-dark"
                    >
                        Comment
                    </button>

                </form>

                <?php if (empty($comments)): ?>

                    <p class="post-detail-empty">
                        No comments yet. Be the first to comment!
                    </p>

                <?php else: ?>

                    <?php foreach ($comments as $comment): ?>

                        <div class="post-detail-comment">

                            <strong>
                                <?php echo postText($comment['username']); ?>
                            </strong>

                            <p>
                                <?php echo nl2br(postText($comment['text'])); ?>
                            </p>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </section>

        <?php else: ?>

            <div class="post-detail-card">
                <p>Post not found.</p>
            </div>

        <?php endif; ?>

    </main>

</body>

</html>

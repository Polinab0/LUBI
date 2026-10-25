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

// Create a database connection
$dbCon = dbCon($user, $pass);

// Create objects
$postObject = new Post($dbCon);
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


// Get all categories from the database
$categoryQuery = $dbCon->prepare(
    "SELECT * FROM category"
);

$categoryQuery->execute();

$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);


// Check if a category was selected
if (isset($_GET['categoryID'])) {

    $selectedCategoryID = $_GET['categoryID'];

    // Get posts from the selected category
    $posts = $postObject->getPostsByCategory(
        $selectedCategoryID
    );

} else {

    // Get all posts
    $posts = $postObject->getAllPosts();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>LUBI - Discover</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>


<!-- Header -->

<header class="header">

    <a
        href="index.php"
        class="wordmark"
    >
        LUBI<span class="wordmark-dot">.</span>
    </a>


    <nav class="main-nav">

        <a
            href="index.php"
            class="active"
        >
            Discover
        </a>

        <a href="#">
            Learn
        </a>

        <a href="createPost.php">
            Create +
        </a>

    </nav>


    <div class="header-right">

        <a href="#">
            Saved
        </a>

        <a href="profile.php">
            <?php echo $_SESSION['username']; ?>
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</header>



<main>


    <!-- Hero -->

    <section class="home-hero page-container">


        <div class="hero-copy">

            <div class="hero-kicker">

                <span class="asterisk">
                    ✳
                </span>

                THE BEAUTY OF WHAT'S NEXT

                <span class="issue">
                    ISSUE 001 — YOUR CREATIVE WORLD
                </span>

            </div>


            <h1>

                Beauty is<br>

                what
                <span class="serif">
                    you
                </span>

                <br>

                make it<span class="wordmark-dot">.</span>

            </h1>


            <div class="hero-bottom">

                <p>
                    A space for the curious, the creators,
                    and everyone becoming.
                    Discover work that moves you.
                    Learn from those who make it.
                </p>


                <a
                    href="#discover-feed"
                    class="circle-arrow"
                >
                    →
                </a>

            </div>

        </div>



        <div class="hero-visual">

            <?php if (!empty($posts) && !empty($posts[0]['file'])) { ?>

                <img
                    src="<?php echo $posts[0]['file']; ?>"
                    alt="Featured beauty work"
                >

            <?php } ?>


            <div class="hero-image-label">

                <span>
                    IN THE SPOTLIGHT
                </span>

                <strong>

                    <?php
                    if (!empty($posts)) {
                        echo $posts[0]['caption'];
                    }
                    ?>

                </strong>

            </div>


            <span class="vertical-caption">

                CREATE · LEARN · GROW · IN BEAUTY

            </span>

        </div>


    </section>



    <!-- Category navigation -->

    <div class="page-container">

        <div class="category-nav">

            <div class="category-label">

                Explore by craft

            </div>


            <div class="category-list">

             <a
                 href="index.php"
                 class="<?php echo !isset($_GET['categoryID']) ? 'active' : ''; ?>"
             >
                 All
               </a>

            <?php foreach ($categories as $category) { ?>

              <a
                  href="index.php?categoryID=<?php echo $category['categoryID']; ?>"
                  class="
                      <?php
                      if (
                          isset($_GET['categoryID']) &&
                          $_GET['categoryID'] == $category['categoryID']
                      ) {
                          echo 'active';
                      }
                      ?>
                  "
              >
                     <?php echo $category['name']; ?>
                     </a>

    <?php } ?>

</div>

        </div>

    </div>



    <!-- Feed -->

    <section
        class="feed-section page-container"
        id="discover-feed"
    >


        <div class="section-heading">

            <div>

                <div class="eyebrow">

                    01 / TRENDING WORK

                </div>


                <h2>

                    Made to
                    <span class="serif">
                        inspire.
                    </span>

                </h2>

            </div>


            <div class="section-heading-side">

                <p>

                    Fresh perspectives,
                    new techniques,
                    and the work worth
                    looking at twice.

                </p>

            </div>

        </div>



        <div class="editorial-grid">


            <?php foreach ($posts as $post) { ?>


                <article class="post-card">


                    <?php if (!empty($post['file'])) { ?>

                        <a
                            href="post.php?postID=<?php echo $post['postID']; ?>"
                            class="post-image-button"
                        >

                            <img
                                src="<?php echo $post['file']; ?>"
                                alt="Beauty post"
                            >

                        </a>

                    <?php } ?>


                    <div class="post-card-meta">

                        <span>

                            <?php echo $post['categoryName']; ?>

                            /

                            <?php echo $post['username']; ?>

                        </span>

                    </div>


                    <a
                        href="post.php?postID=<?php echo $post['postID']; ?>"
                        class="post-card-title"
                    >

                        <?php echo $post['caption']; ?>

                        <span>
                            ↗
                        </span>

                    </a>


                    <?php

                    $likeCount =
                        $postObject->getLikeCount(
                            $post['postID']
                        );

                    $commentCount =
                        $commentObject->getCommentCount(
                            $post['postID']
                        );

                    ?>


                    <div class="post-stats">

                        <span>
                            ♡ <?php echo $likeCount; ?>
                        </span>

                        <span>
                            ◌ <?php echo $commentCount; ?>
                        </span>

                    </div>


                </article>


            <?php } ?>


        </div>


    </section>


</main>


</body>

</html>

<?php

// Include session functions
require_once 'includes/session.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Only admins can access this page
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

require_once 'dbcon.php';
require_once 'classes/Post.php';
require_once "classes/Course.php";

$database = new Database();
$dbCon = $database->connect();

$postObject = new Post($dbCon);
$courseObject = new Course($dbCon);

$courses = $courseObject->getAllCourses();

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    if (isset($_POST['deletePost'])) {

        $postID = filter_input(INPUT_POST, 'postID', FILTER_VALIDATE_INT);

        if (!$postID) {
            die("Invalid post ID.");
        }

        if (!$postObject->deletePost($postID)) {
            die("Could not delete the post.");
        }
    }

     if (isset($_POST['deleteCourse'])) {

          $courseID = filter_input(
             INPUT_POST,
             'courseID',
             FILTER_VALIDATE_INT
          );

        if (!$courseID) {
            die("Invalid course ID.");
        }

      $courseObject->deleteCourse($courseID);
    }

    if (isset($_POST['blockUser']) || isset($_POST['unblockUser'])) {

        $userID = filter_input(INPUT_POST, 'userID', FILTER_VALIDATE_INT);

        if (!$userID) {
            die("Invalid user ID.");
        }

        // Do not allow an admin to block themselves
        if ($userID == $_SESSION['userID']) {
            die("You cannot block yourself.");
        }

        $isBlocked = isset($_POST['blockUser']) ? 1 : 0;

        $query = $dbCon->prepare(
            "UPDATE user
             SET isBlocked = :isBlocked
             WHERE userID = :userID
             AND userType != 'admin'"
        );

        $query->bindValue(':isBlocked', $isBlocked, PDO::PARAM_INT);
        $query->bindValue(':userID', $userID, PDO::PARAM_INT);

        $query->execute();
    }

    header("Location: admin.php");
    exit();
}

$posts = $postObject->getAllPosts();

$totalPosts = count(array_unique(array_column($posts, 'postID')));
$totalCourses = count($courses);

$query = $dbCon->prepare("SELECT COUNT(*) FROM user");
$query->execute();
$totalUsers = $query->fetchColumn();

// Get all users, including blocked status
$query = $dbCon->prepare(
    "SELECT userID, userType, username, email, city, isBlocked
     FROM user"
);

$query->execute();
$users = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LUBI Admin Panel</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
</head>

<body>

    <!-- Header -->
    <header class="header">

        <a href="index.php" class="wordmark">
            LUBI<span class="wordmark-dot">.</span>
        </a>

        <nav class="main-nav">
            <a href="index.php">Home</a>
            <a href="admin.php">Admin Panel</a>
        </nav>

        <div class="header-right">
            <a href="logout.php" class="button-dark">Logout</a>
        </div>


    </header>

    <main class="page-container admin-page">

        <!-- Page heading -->
        <div class="admin-heading">

            <span class="eyebrow">LUBI / ADMINISTRATION</span>

            <h1>Admin <span class="serif">Dashboard.</span></h1>

            <p>Manage users, posts and community activity.</p>

        </div>

        <!-- Dashboard statistics -->
        <div class="admin-stats">

            <div class="admin-stat-card">
                <span>Total Users</span>

                <strong>
                    <?php echo htmlspecialchars((string) $totalUsers); ?>
                </strong>
            </div>

            <div class="admin-stat-card">
                <span>Total Posts</span>

                <strong>
                    <?php echo htmlspecialchars((string) $totalPosts); ?>
                </strong>
            </div>

            <div class="admin-stat-card">
                <span>Total Courses</span>
                <strong><?php echo htmlspecialchars((string) $totalCourses); ?></strong>
            </div>
        </div>

        <!-- Users section -->
        <section class="admin-section">

            <h2>Manage <span class="serif">Users.</span></h2>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>City</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($users as $user) { ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars((string) $user['userID']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['userType']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['city'] ?? ''); ?>
                                </td>

                                <td>

                                    <div class="admin-actions">

                                        <?php if ($user['userType'] == 'admin') { ?>

                                            <span>Admin</span>

                                        <?php } else { ?>

                                            <form action="admin.php" method="POST">

                                                <input
                                                    type="hidden"
                                                    name="userID"
                                                    value="<?php echo htmlspecialchars((string) $user['userID']); ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?php echo htmlspecialchars(csrf_token()); ?>"
                                                >

                                                <?php if ($user['isBlocked'] == 1) { ?>

                                                    <button
                                                        type="submit"
                                                        name="unblockUser"
                                                        class="admin-btn admin-btn-secondary"
                                                    >
                                                        Unblock
                                                    </button>

                                                <?php } else { ?>

                                                    <button
                                                        type="submit"
                                                        name="blockUser"
                                                        class="admin-btn admin-btn-danger"
                                                    >
                                                        Block
                                                    </button>


                                                <?php } ?>

                                            </form>

                                        <?php } ?>

                                    </div>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- Posts section -->
        <section class="admin-section">

            <h2>Manage <span class="serif">Posts.</span></h2>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Post ID</th>
                            <th>Username</th>
                            <th>Category</th>
                            <th>Caption</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($posts as $post) { ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars((string) $post['postID']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($post['username']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($post['categoryName']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($post['caption']); ?>
                                </td>

                                <td>

                                    <div class="admin-actions">

                                        <a
                                            href="post.php?postID=<?php echo urlencode((string) $post['postID']); ?>"
                                            class="admin-btn admin-btn-secondary"
                                        >
                                            View Post
                                        </a>

                                        <form action="admin.php" method="POST">

                                            <input
                                                type="hidden"
                                                name="postID"
                                                value="<?php echo htmlspecialchars((string) $post['postID']); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo htmlspecialchars(csrf_token()); ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="deletePost"
                                                class="admin-btn admin-btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this post?');"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </section>

        <section class="admin-section">
    <!-- Courses table -->
<h2>Manage <span class="serif">Courses.</span></h2>

<div class="admin-table-wrapper">
<table class="admin-table">

    <tr>
        <th>Course ID</th>
        <th>Title</th>
        <th>Professional</th>
        <th>Category</th>
        <th>Created</th>
        <th>Action</th>
    </tr>

    <?php foreach ($courses as $course) { ?>

        <tr>

            <td>
                <?php echo htmlspecialchars(
                    $course['courseID'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </td>

            <td>
                <?php echo htmlspecialchars(
                    $course['title'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </td>

            <td>
                <?php echo htmlspecialchars(
                    $course['professionalName'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </td>

            <td>
                <?php echo htmlspecialchars(
                    $course['categoryName'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </td>

            <td>
                <?php echo htmlspecialchars(
                    $course['createdAt'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </td>

            <td>

                <a class="admin-btn admin-btn-secondary" href="course.php?courseID=<?php echo (int) $course['courseID']; ?>">
                    View Course
                </a>

                <form action="admin.php" method="POST">

                    <input
                        type="hidden"
                        name="courseID"
                        value="<?php echo (int) $course['courseID']; ?>"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars(
                            csrf_token(),
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                    >

                    <button
                        type="submit"
                        name="deleteCourse"
                        class="admin-btn admin-btn-danger"
                        onclick="return confirm('Are you sure you want to delete this course?');"
                    >
                        Delete
                    </button>

                </form>

            </td>

        </tr>

    <?php } ?>

</table>
</div>

        </section>

        <!-- Footer links -->
        <div class="admin-footer">

            <a href="index.php">Back to Home</a>

            <a href="logout.php">Logout</a>

        </div>

    </main>

</body>

</html>

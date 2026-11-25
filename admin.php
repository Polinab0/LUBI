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

// Get all users, including their blocked status
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
    <title>LUBI Admin Panel</title>
</head>

<body>

    <h1>LUBI Admin Panel</h1>

    <p>Welcome to the admin area.</p>

    <h2>Dashboard</h2>

    <p>Total users: <?php echo $totalUsers; ?></p>
    <p>Total posts: <?php echo $totalPosts; ?></p>
    <p>Total courses: <?php echo $totalCourses; ?></p>

    <!-- Users table -->
    <h2>Users</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>User Type</th>
            <th>City</th>
            <th>Action</th>
        </tr>

        <?php foreach ($users as $user) { ?>

            <tr>
                <td><?php echo htmlspecialchars($user['userID']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['userType']); ?></td>
                <td><?php echo htmlspecialchars($user['city'] ?? ''); ?></td>

                <td>

                    <?php if ($user['userType'] == 'admin') { ?>

                        Admin

                    <?php } else { ?>

                        <form action="admin.php" method="POST">

                            <input
                                type="hidden"
                                name="userID"
                                value="<?php echo $user['userID']; ?>"
                            >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo csrf_token(); ?>"
                            >

                            <?php if ($user['isBlocked'] == 1) { ?>

                                <button type="submit" name="unblockUser">
                                    Unblock
                                </button>

                            <?php } else { ?>

                                <button type="submit" name="blockUser">
                                    Block
                                </button>

                            <?php } ?>

                        </form>

                    <?php } ?>

                </td>
            </tr>

        <?php } ?>

    </table>

    <!-- Posts table -->
    <h2>Posts</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>Post ID</th>
            <th>Username</th>
            <th>Category</th>
            <th>Caption</th>
            <th>Action</th>
        </tr>

        <?php foreach ($posts as $post) { ?>

            <tr>
                <td><?php echo htmlspecialchars($post['postID']); ?></td>
                <td><?php echo htmlspecialchars($post['username']); ?></td>
                <td><?php echo htmlspecialchars($post['categoryName']); ?></td>
                <td><?php echo htmlspecialchars($post['caption']); ?></td>

                <td>

                    <a href="post.php?postID=<?php echo $post['postID']; ?>">
                        View Post
                    </a>

                    <form action="admin.php" method="POST">

                        <input
                            type="hidden"
                            name="postID"
                            value="<?php echo $post['postID']; ?>"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo csrf_token(); ?>"
                        >

                        <button type="submit" name="deletePost">
                            Delete
                        </button>

                    </form>

                </td>
            </tr>

        <?php } ?>

    </table>


    <!-- Courses table -->
<h2>Courses</h2>

<table border="1" cellpadding="10">

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

                <a href="course.php?courseID=<?php echo (int) $course['courseID']; ?>">
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
                    >
                        Delete
                    </button>

                </form>

            </td>

        </tr>

    <?php } ?>

</table>

    <br>

    <a href="index.php">Home</a>
    <br>
    <a href="logout.php">Logout</a>

</body>

</html>

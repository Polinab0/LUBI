
<?php

// Include session functions
require_once 'includes/session.php';

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Only admins can access this page
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

// Connect to the database
require_once 'dbcon.php';
require_once 'classes/Post.php';
require_once 'classes/User.php';

$database = new Database();
$dbCon = $database->connect();

// Create objects
$postObject = new Post($dbCon);
$userObject = new User($dbCon);

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Check CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // Delete a post
    if (isset($_POST['deletePost'])) {

        $postID = filter_input(INPUT_POST, 'postID', FILTER_VALIDATE_INT);

        if (!$postID) {
            die("Invalid post ID.");
        }

        if (!$postObject->deletePost($postID)) {
            die("Could not delete the post.");
        }
    }

    // Block or unblock a user
    if (isset($_POST['blockUser']) || isset($_POST['unblockUser'])) {

        $userID = filter_input(INPUT_POST, 'userID', FILTER_VALIDATE_INT);

        if (!$userID) {
            die("Invalid user ID.");
        }

        // Do not allow an admin to block themselves
        if ($userID == $_SESSION['userID']) {
            die("You cannot block yourself.");
        }

        // Set blocked status
        $isBlocked = isset($_POST['blockUser']) ? 1 : 0;

        // Use the User class to block or unblock
        if (!$userObject->setBlockedStatus($userID, $isBlocked)) {
            die("Could not update the user.");
        }
    }

    // Refresh the admin page
    header("Location: admin.php");
    exit();
}

// Get all posts
$posts = $postObject->getAllPosts();

// Count unique posts
$totalPosts = count(array_unique(array_column($posts, 'postID')));

// Count users
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

    <br>

    <a href="index.php">Home</a>
    <br>
    <a href="logout.php">Logout</a>

</body>

</html>

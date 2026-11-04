
<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Check if the user is an admin
if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

// Connect to the database
require_once 'dbcon.php';

$database = new Database();
$dbCon = $database->connect();

// Get the total number of users
$query = $dbCon->prepare(
    "SELECT COUNT(*) FROM user"
);

$query->execute();

$totalUsers = $query->fetchColumn();

// Get all users
$query = $dbCon->prepare(
    "SELECT userID, userType, username, email, city
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

    <h2>Users</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>User Type</th>
            <th>City</th>
        </tr>

        <?php foreach ($users as $user) { ?>

            <tr>
                <td><?php echo htmlspecialchars($user['userID']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['userType']); ?></td>
                <td><?php echo htmlspecialchars($user['city'] ?? ''); ?></td>
            </tr>

        <?php } ?>

    </table>

    <br>

    <a href="index.php">Home</a>
    <br>
    <a href="logout.php">Logout</a>

</body>

</html>

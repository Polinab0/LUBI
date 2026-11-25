<?php

require_once "includes/session.php";
confirm_logged_in();

require_once "dbcon.php";
require_once "classes/Course.php";

// Set up the database and Course class
$database = new Database();
$dbCon = $database->connect();
$courseObject = new Course($dbCon);

if (
    $_SESSION['userType'] !== 'professional'
    && $_SESSION['userType'] !== 'admin'
) {
    die("Access denied.");
}

$categoryQuery = $dbCon->prepare(
    "SELECT categoryID, name
     FROM category
     ORDER BY name"
);

$categoryQuery->execute();

$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Handle course creation
if (isset($_POST['submit'])) {

    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $categoryID = filter_input(
        INPUT_POST,
        'categoryID',
        FILTER_VALIDATE_INT
    );

    if (empty($title)) {
        die("Title is required.");
    }

    if (strlen($title) > 150) {
        die("Title is too long.");
    }

    if (empty($description)) {
        die("Description is required.");
    }

    if ($categoryID === false || $categoryID === null) {
        die("Invalid category.");
    }

    // Check that the category actually exists
    $categoryCheck = $dbCon->prepare(
        "SELECT categoryID
         FROM category
         WHERE categoryID = :categoryID"
    );

    $categoryCheck->execute([
        ':categoryID' => $categoryID
    ]);

    if (!$categoryCheck->fetch(PDO::FETCH_ASSOC)) {
        die("Category does not exist.");
    }

    // The logged in professional becomes the course owner
    $professionalID = $_SESSION['userID'];

    $courseID = $courseObject->createCourse(
        $title,
        $description,
        $professionalID,
        $categoryID
    );

    header("Location: course.php?courseID=" . $courseID);
    exit();
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

    <title>LUBI - Create Course</title>
</head>

<body>

    <h1>Create Course</h1>

    <form
        action="createCourse.php"
        method="post"
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

        <label for="title">
            Title:
        </label>

        <input
            type="text"
            name="title"
            id="title"
            maxlength="150"
            required
        >

        <br><br>

        <label for="description">
            Description:
        </label>

        <textarea
            name="description"
            id="description"
            required
        ></textarea>

        <br><br>

        <label for="categoryID">
            Category:
        </label>

        <select
            name="categoryID"
            id="categoryID"
            required
        >

            <?php foreach ($categories as $category) { ?>

                <option
                    value="<?php echo (int) $category['categoryID']; ?>"
                >
                    <?php echo htmlspecialchars(
                        $category['name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </option>

            <?php } ?>

        </select>

        <br><br>

        <input
            type="submit"
            name="submit"
            value="Create Course"
        >

    </form>

    <br>

    <a href="courses.php">
        Back to Courses
    </a>

</body>

</html>
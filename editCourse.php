<?php

require_once "includes/session.php";
confirm_logged_in();

require_once "dbcon.php";
require_once "classes/Course.php";

$database = new Database();
$dbCon = $database->connect();

$courseObject = new Course($dbCon);

// Check courseID
$courseID = filter_input(
    INPUT_GET,
    'courseID',
    FILTER_VALIDATE_INT
);

if (!$courseID) {
    header("Location: courses.php");
    exit();
}

$course = $courseObject->getCourseById($courseID);

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Only the course owner or admin can edit
if (
    $course['professionalID'] != $_SESSION['userID']
    && $_SESSION['userType'] !== 'admin'
) {
    die("Access denied.");
}

// Get categories
$categoryQuery = $dbCon->prepare(
    "SELECT categoryID, name
     FROM category
     ORDER BY name"
);

$categoryQuery->execute();

$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Update the course
if (isset($_POST['updateCourse'])) {

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

    $courseObject->updateCourse(
        $courseID,
        $title,
        $description,
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
    <title>LUBI - Edit Course</title>
</head>

<body>

    <h1>Edit Course</h1>

    <form method="post">

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
            value="<?php echo htmlspecialchars(
                $course['title'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
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
        ><?php echo htmlspecialchars(
            $course['description'],
            ENT_QUOTES,
            'UTF-8'
        ); ?></textarea>

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
                    <?php
                    if ($category['name'] === $course['categoryName']) {
                        echo 'selected';
                    }
                    ?>
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

        <button
            type="submit"
            name="updateCourse"
        >
            Update Course
        </button>

    </form>

    <br>

    <a href="course.php?courseID=<?php echo (int) $courseID; ?>">
        Back to Course
    </a>

</body>

</html>
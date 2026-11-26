<?php

require_once "includes/session.php";
confirm_logged_in();

require_once "dbcon.php";
require_once "classes/Course.php";

$database = new Database();
$dbCon = $database->connect();

$courseObject = new Course($dbCon);

$userID = $_SESSION['userID'];

$courses = $courseObject->getCoursesByUser($userID);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - My Courses</title>
</head>

<body>

    <h1>My Courses</h1>

    <a href="courses.php">
        Back to Courses
    </a>

    <hr>

    <?php if (!empty($courses)) { ?>

        <?php foreach ($courses as $course) { ?>

            <div>

                <h2>
                    <?php echo htmlspecialchars(
                        $course['title'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </h2>

                <p>
                    Category:
                    <?php echo htmlspecialchars(
                        $course['categoryName'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </p>

                <p>
                    Professional:
                    <?php echo htmlspecialchars(
                        $course['professionalName'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </p>

                <p>
                    <?php echo htmlspecialchars(
                        $course['description'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </p>

                <a href="course.php?courseID=<?php echo (int) $course['courseID']; ?>">
                    View Course
                </a>

                <hr>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>You have not enrolled in any courses yet.</p>

    <?php } ?>

</body>

</html>
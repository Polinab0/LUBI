<?php

require_once "includes/session.php";
confirm_logged_in();

require_once "dbcon.php";
require_once "classes/Course.php";

$database = new Database();
$dbCon = $database->connect();

$courseObject = new Course($dbCon);

$courses = $courseObject->getAllCourses();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Courses</title>
</head>

<body>

    <h1>Courses</h1>

    <a href="index.php">Back to Home</a>

    <a href="myCourses.php">
      My Courses
    </a>

       <?php if (
         $_SESSION['userType'] === 'professional'
         || $_SESSION['userType'] === 'admin'
       ) { ?>

    <a href="createCourse.php">
        Create Course
    </a>

<?php } ?>

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

                <a href="course.php?courseID=<?php echo $course['courseID']; ?>">
                    View Course
                </a>

                <hr>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>No courses available yet.</p>

    <?php } ?>

</body>

</html>
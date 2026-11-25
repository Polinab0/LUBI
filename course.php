<?php

require_once "includes/session.php";
confirm_logged_in();

require_once "dbcon.php";
require_once "classes/Course.php";

$database = new Database();
$dbCon = $database->connect();

$courseObject = new Course($dbCon);

// Check if courseID exists in the URL
if (isset($_GET['courseID'])) {

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

    $userID = $_SESSION['userID'];

    $isEnrolled = $courseObject->isUserEnrolled(
        $userID,
        $courseID
    );

    // Enroll the user
       if (
         isset($_POST['enroll'])
         && $_SESSION['userType'] === 'learner'
        ) {

        if (
            !isset($_POST['csrf_token'])
            || !verify_csrf_token($_POST['csrf_token'])
        ) {
            die("Invalid CSRF token.");
        }

        if (!$isEnrolled) {

            $courseObject->enrollUser(
                $userID,
                $courseID
            );
        }

        header("Location: course.php?courseID=" . $courseID);
        exit();
    }

    // Remove the user from the course
   if (
      isset($_POST['unenroll'])
      && $_SESSION['userType'] === 'learner'
   ) {

    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    if ($isEnrolled) {

        $courseObject->unenrollUser(
            $userID,
            $courseID
        );
    }

    header("Location: course.php?courseID=" . $courseID);
    exit();
}


// Delete the course
if (
    isset($_POST['deleteCourse'])
    && (
        $course['professionalID'] == $_SESSION['userID']
        || $_SESSION['userType'] === 'admin'
    )
) {

    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    $courseObject->deleteCourse($courseID);

    header("Location: courses.php");
    exit();
}

    }  else {

        header("Location: courses.php");
         exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LUBI - Course</title>
</head>

<body>

    <a href="courses.php">Back to Courses</a>

    <hr>

    <?php if ($course) { ?>

        <h1>
            <?php echo htmlspecialchars(
                $course['title'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </h1>

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

        <p>
            Created:
            <?php echo htmlspecialchars(
                $course['createdAt'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </p>

         <?php if (
             $_SESSION['userType'] === 'learner'
             && !$isEnrolled
        ) { ?>

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

        <button
            type="submit"
            name="enroll"
        >
            Enroll
        </button>

    </form>

    <?php } elseif (
           $_SESSION['userType'] === 'learner'
           && $isEnrolled
        ) { ?>

          <p>
             Enrolled
          </p>

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

             <button
                 type="submit"
                 name="unenroll"
            >
            Leave Course
        </button>

         </form>

    <?php } ?>

    <?php if (
         $course['professionalID'] == $_SESSION['userID']
         || $_SESSION['userType'] === 'admin'
    ) { ?>

        <a href="editCourse.php?courseID=<?php echo (int) $courseID; ?>">
             Edit Course
        </a>

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

        <button
            type="submit"
            name="deleteCourse"
        >
            Delete Course
        </button>

    </form>

<?php } ?>

     <?php } else { ?>

        <p>Course not found.</p>

    <?php } ?>

</body>

</html>
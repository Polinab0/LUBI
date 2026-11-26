<?php

// Include session functions
require_once "includes/session.php";
confirm_logged_in();

// Include database and Course class
require_once "dbcon.php";
require_once "classes/Course.php";

// Connect to database
$database = new Database();
$dbCon = $database->connect();

// Create Course object
$courseObject = new Course($dbCon);

// Get course ID from URL
$courseID = filter_input(
    INPUT_GET,
    'courseID',
    FILTER_VALIDATE_INT
);

if (!$courseID) {
    header("Location: courses.php");
    exit();
}

// Get course information
$course = $courseObject->getCourseById($courseID);

if (!$course) {
    http_response_code(404);
}

// Get logged in user
$userID = $_SESSION['userID'];

// Check enrollment only if course exists
$isEnrolled = false;

if ($course) {
    $isEnrolled = $courseObject->isUserEnrolled(
        $userID,
        $courseID
    );
}

// Check if user can manage this course
$canManage = false;

if ($course) {
    $canManage = (
        $course['professionalID'] == $userID ||
        $_SESSION['userType'] === 'admin'
    );
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    if (!$course) {
        die("Course not found.");
    }

    // Enroll learner
    if (
        isset($_POST['enroll']) &&
        $_SESSION['userType'] === 'learner'
    ) {
        if (!$isEnrolled) {
            $courseObject->enrollUser($userID, $courseID);
        }

        header("Location: course.php?courseID=" . $courseID);
        exit();
    }

    // Unenroll learner
    if (
        isset($_POST['unenroll']) &&
        $_SESSION['userType'] === 'learner'
    ) {
        if ($isEnrolled) {
            $courseObject->unenrollUser($userID, $courseID);
        }

        header("Location: course.php?courseID=" . $courseID);
        exit();
    }

    // Delete course
    if (isset($_POST['deleteCourse']) && $canManage) {

        $courseObject->deleteCourse($courseID);

        header("Location: courses.php");
        exit();
    }
}

// Escape text safely
function courseText($value) {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LUBI — Course Details</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        .course-detail-page {
            max-width: 950px;
            margin: 70px auto 110px;
            padding: 0 24px;
        }

        .course-detail-back {
            display: inline-block;
            margin-bottom: 35px;
            color: #51564b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .course-detail-back:hover {
            text-decoration: underline;
        }

        .course-detail-heading {
            margin-bottom: 35px;
        }

        .course-detail-heading h1 {
            font-size: clamp(38px, 6vw, 60px);
            line-height: 1.15;
            margin: 12px 0;
            color: #22251f;
            overflow-wrap: anywhere;
        }

        .course-detail-heading h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .course-detail-heading p {
            color: #777a70;
            font-size: 15px;
        }

        .course-detail-card {
            background: #ffffff;
            border: 1px solid #e5e6df;
            padding: 40px;
        }

        .course-detail-category {
            display: inline-block;
            background: #e9eddf;
            color: #444c39;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .course-detail-card h2 {
            margin: 25px 0 15px;
            font-size: clamp(27px, 4vw, 38px);
            line-height: 1.25;
            color: #252820;
            overflow-wrap: anywhere;
        }

        .course-detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e8e9e3;
            color: #777a70;
            font-size: 14px;
        }

        .course-detail-meta strong {
            color: #343a30;
        }

        .course-detail-description {
            margin: 30px 0;
        }

        .course-detail-description h3 {
            font-size: 20px;
            color: #252820;
            margin-bottom: 15px;
        }

        .course-detail-description p {
            color: #53594e;
            line-height: 1.9;
            font-size: 16px;
            overflow-wrap: anywhere;
        }

        .course-detail-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            padding-top: 25px;
            border-top: 1px solid #e8e9e3;
        }

        .course-detail-actions form {
            margin: 0;
        }

        .course-detail-button {
            display: inline-block;
            padding: 14px 24px;
            background: #242720;
            color: #ffffff;
            border: 1px solid #242720;
            text-decoration: none;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .course-detail-button:hover {
            background: #41483a;
        }

        .course-detail-button-light {
            background: #e9eddf;
            border-color: #d6dbcb;
            color: #242820;
        }

        .course-detail-button-light:hover {
            background: #d8e1ca;
        }

        .course-detail-button-danger {
            background: #aa4c4c;
            border-color: #aa4c4c;
            color: #ffffff;
        }

        .course-detail-button-danger:hover {
            background: #903939;
        }

        .course-detail-enrolled {
            display: inline-block;
            padding: 12px 18px;
            background: #e9eddf;
            color: #444c39;
            font-size: 13px;
            font-weight: 700;
        }

        .course-detail-not-found {
            background: #e9ebe2;
            padding: 45px;
            text-align: center;
        }

        .course-detail-not-found h2 {
            margin-bottom: 12px;
        }

        @media (max-width: 600px) {
            .course-detail-page {
                margin-top: 40px;
                padding: 0 16px;
            }

            .course-detail-card {
                padding: 24px;
            }

            .course-detail-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .course-detail-button {
                width: 100%;
                box-sizing: border-box;
                text-align: center;
            }
        }
    </style>

</head>

<body>

    <!-- LUBI Header -->
    <header class="header">

        <a href="index.php" class="wordmark">
            LUBI<span class="wordmark-dot">.</span>
        </a>

        <nav class="main-nav">
            <a href="index.php">Home</a>
            <a href="courses.php">Courses</a>
            <a href="myCourses.php">My Courses</a>
            <a href="profile.php">My Profile</a>

            <?php if (
                isset($_SESSION['userType']) &&
                $_SESSION['userType'] === 'admin'
            ): ?>
                <a href="admin.php">Admin Panel</a>
            <?php endif; ?>
        </nav>

        <div class="header-right">
            <a href="logout.php" class="button-dark">
                Logout
            </a>
        </div>

    </header>

    <main class="course-detail-page">

        <a href="courses.php" class="course-detail-back">
            ← Back to Courses
        </a>

        <div class="course-detail-heading">

            <span class="eyebrow">
                LUBI / LEARN & GROW
            </span>

            <h1>
                Course <em>Details.</em>
            </h1>

            <p>
                Explore new skills and grow your beauty expertise.
            </p>

        </div>

        <?php if ($course): ?>

            <article class="course-detail-card">

                <!-- Category -->
                <span class="course-detail-category">
                    <?php echo courseText($course['categoryName']); ?>
                </span>

                <!-- Course title -->
                <h2>
                    <?php echo courseText($course['title']); ?>
                </h2>

                <!-- Course information -->
                <div class="course-detail-meta">

                    <span>
                        <strong>Professional:</strong>
                        <?php echo courseText($course['professionalName']); ?>
                    </span>

                    <span>
                        <strong>Created:</strong>
                        <?php echo courseText($course['createdAt']); ?>
                    </span>

                </div>

                <!-- Description -->
                <div class="course-detail-description">

                    <h3>About This Course</h3>

                    <p>
                        <?php echo nl2br(
                            courseText($course['description'])
                        ); ?>
                    </p>

                </div>

                <!-- Course actions -->
                <div class="course-detail-actions">

                    <!-- Enroll -->
                    <?php if (
                        $_SESSION['userType'] === 'learner' &&
                        !$isEnrolled
                    ): ?>

                        <form method="post">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo courseText(csrf_token()); ?>"
                            >

                            <button
                                type="submit"
                                name="enroll"
                                class="course-detail-button"
                            >
                                Enroll in Course
                            </button>

                        </form>

                    <?php elseif (
                        $_SESSION['userType'] === 'learner' &&
                        $isEnrolled
                    ): ?>

                        <span class="course-detail-enrolled">
                            Enrolled
                        </span>

                        <form method="post">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo courseText(csrf_token()); ?>"
                            >

                            <button
                                type="submit"
                                name="unenroll"
                                class="course-detail-button course-detail-button-light"
                            >
                                Leave Course
                            </button>

                        </form>

                    <?php endif; ?>

                    <!-- Edit / Delete -->
                    <?php if ($canManage): ?>

                        <a
                            href="editCourse.php?courseID=<?php echo (int) $courseID; ?>"
                            class="course-detail-button course-detail-button-light"
                        >
                            Edit Course
                        </a>

                        <form
                            method="post"
                            onsubmit="return confirm('Are you sure you want to delete this course?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo courseText(csrf_token()); ?>"
                            >

                            <button
                                type="submit"
                                name="deleteCourse"
                                class="course-detail-button course-detail-button-danger"
                            >
                                Delete Course
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </article>

        <?php else: ?>

            <div class="course-detail-not-found">

                <h2>Course not found.</h2>

                <p>
                    This course may have been removed.
                </p>

                <a
                    href="courses.php"
                    class="course-detail-button"
                >
                    Browse Courses
                </a>

            </div>

        <?php endif; ?>

    </main>

</body>

</html>

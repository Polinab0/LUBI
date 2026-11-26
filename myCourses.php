<?php

require_once "includes/session.php";
confirm_logged_in();

require_once "dbcon.php";
require_once "classes/Course.php";

// Connect to database
$database = new Database();
$dbCon = $database->connect();

// Create Course object
$courseObject = new Course($dbCon);

// Get logged in user
$userID = $_SESSION['userID'];

// Get courses for this user
$courses = $courseObject->getCoursesByUser($userID);

// Escape text safely
function myCourseText($value) {
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

    <title>LUBI — My Courses</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        .my-courses-page {
            max-width: 1100px;
            margin: 70px auto 110px;
            padding: 0 24px;
        }

        .my-courses-back {
            display: inline-block;
            margin-bottom: 35px;
            color: #51564b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .my-courses-back:hover {
            text-decoration: underline;
        }

        .my-courses-heading {
            margin-bottom: 35px;
        }

        .my-courses-heading h1 {
            font-size: clamp(38px, 6vw, 60px);
            line-height: 1.15;
            margin: 12px 0;
            color: #22251f;
        }

        .my-courses-heading h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .my-courses-heading p {
            color: #777a70;
            font-size: 15px;
            line-height: 1.7;
        }

        .my-courses-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .my-courses-count {
            display: inline-block;
            padding: 12px 18px;
            background: #e9eddf;
            color: #444c39;
            font-size: 13px;
            font-weight: 700;
        }

        .my-courses-button {
            display: inline-block;
            padding: 14px 24px;
            background: #242720;
            border: 1px solid #242720;
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }

        .my-courses-button:hover {
            background: #41483a;
        }

        .my-courses-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }

        .my-course-card {
            background: #ffffff;
            border: 1px solid #e5e6df;
            padding: 35px;
        }

        .my-course-category {
            display: inline-block;
            background: #e9eddf;
            color: #444c39;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 700;
        }

        .my-course-card h2 {
            margin: 24px 0 12px;
            font-size: 27px;
            line-height: 1.3;
            color: #252820;
            overflow-wrap: anywhere;
        }

        .my-course-professional {
            color: #777a70;
            font-size: 14px;
        }

        .my-course-professional strong {
            color: #343a30;
        }

        .my-course-description {
            margin: 22px 0;
            color: #53594e;
            line-height: 1.8;
            font-size: 15px;
            overflow-wrap: anywhere;
        }

        .my-course-footer {
            margin-top: 25px;
            padding-top: 22px;
            border-top: 1px solid #e8e9e3;
        }

        .my-courses-empty {
            background: #e9ece3;
            padding: 55px 30px;
            text-align: center;
        }

        .my-courses-empty h2 {
            margin: 0 0 15px;
            font-size: 28px;
            color: #252820;
        }

        .my-courses-empty p {
            color: #777a70;
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 25px;
        }

        @media (max-width: 700px) {
            .my-courses-page {
                margin-top: 40px;
                padding: 0 16px;
            }

            .my-courses-grid {
                grid-template-columns: 1fr;
            }

            .my-course-card {
                padding: 24px;
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
            <a href="saved.php">Saved Posts</a>
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

    <main class="my-courses-page">

        <a href="courses.php" class="my-courses-back">
            ← Back to Courses
        </a>

        <!-- Page heading -->
        <div class="my-courses-heading">

            <span class="eyebrow">
                LUBI / YOUR LEARNING
            </span>

            <h1>
                My <em>Courses.</em>
            </h1>

            <p>
                Keep track of your beauty courses and
                continue developing your skills.
            </p>

        </div>

        <!-- Course count and browse button -->
        <div class="my-courses-toolbar">

            <span class="my-courses-count">
                <?php echo count($courses); ?>
                <?php echo count($courses) === 1 ? 'Course' : 'Courses'; ?>
            </span>

            <a href="courses.php" class="my-courses-button">
                Browse Courses
            </a>

        </div>

        <!-- Course list -->
        <?php if (!empty($courses)): ?>

            <div class="my-courses-grid">

                <?php foreach ($courses as $course): ?>

                    <article class="my-course-card">

                        <!-- Category -->
                        <span class="my-course-category">
                            <?php echo myCourseText($course['categoryName']); ?>
                        </span>

                        <!-- Course title -->
                        <h2>
                            <?php echo myCourseText($course['title']); ?>
                        </h2>

                        <!-- Professional -->
                        <p class="my-course-professional">
                            <strong>Professional:</strong>
                            <?php echo myCourseText($course['professionalName']); ?>
                        </p>

                        <!-- Description -->
                        <p class="my-course-description">
                            <?php echo nl2br(
                                myCourseText($course['description'])
                            ); ?>
                        </p>

                        <!-- View course -->
                        <div class="my-course-footer">

                            <a
                                href="course.php?courseID=<?php echo (int) $course['courseID']; ?>"
                                class="my-courses-button"
                            >
                                View Course
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <!-- Empty state -->
            <div class="my-courses-empty">

                <h2>No courses yet.</h2>

                <p>
                    You haven't enrolled in any courses yet.
                    Explore available courses and start learning!
                </p>

                <a href="courses.php" class="my-courses-button">
                    Explore Courses
                </a>

            </div>

        <?php endif; ?>

    </main>

</body>

</html>

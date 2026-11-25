
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUBI - Courses</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f8f8f5;
            color: #252820;
            font-family: Arial, sans-serif;
        }

        .lubi-nav {
            display: flex;
            align-items: center;
            gap: 45px;
            padding: 25px 4%;
            border-bottom: 1px solid #e5e5df;
            flex-wrap: wrap;
        }

        .lubi-logo {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -2px;
            text-decoration: none;
            color: #252820;
        }

        .lubi-logo span {
            color: #a9b795;
        }

        .lubi-links {
            display: flex;
            align-items: center;
            gap: 35px;
            flex-wrap: wrap;
        }

        .lubi-links a {
            color: #74776e;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .lubi-links a:hover {
            color: #252820;
        }

        .logout-btn {
            margin-left: auto;
            padding: 15px 25px;
            background: #252820;
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .courses-page {
            max-width: 1100px;
            margin: 75px auto;
            padding: 0 25px;
        }

        .back-link {
            color: #50574b;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .eyebrow {
            margin-top: 45px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        h1 {
            font-size: clamp(45px, 6vw, 70px);
            font-weight: 500;
            letter-spacing: -4px;
            margin: 20px 0;
        }

        h1 em {
            font-family: Georgia, serif;
            font-weight: normal;
        }

        .subtitle {
            color: #777b70;
            font-size: 16px;
            margin-bottom: 35px;
        }

        .course-actions {
            display: flex;
            gap: 12px;
            margin-bottom: 45px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 15px 24px;
            background: #252820;
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            border: none;
        }

        .btn-light {
            background: #e8eddf;
            color: #252820;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 25px;
        }

        .course-card {
            background: white;
            border: 1px solid #e4e5de;
            padding: 35px;
        }

        .course-category {
            display: inline-block;
            padding: 10px 16px;
            background: #e8eddf;
            color: #454d3c;
            font-size: 12px;
            font-weight: bold;
        }

        .course-card h2 {
            font-size: 27px;
            margin: 25px 0 12px;
            letter-spacing: -1px;
        }

        .professional {
            color: #777b70;
            font-size: 14px;
        }

        .description {
            color: #55594f;
            line-height: 1.7;
            margin: 22px 0;
            overflow-wrap: anywhere;
        }

        .card-footer {
            border-top: 1px solid #e5e5df;
            padding-top: 22px;
            margin-top: 25px;
        }

        .empty-state {
            background: #e9ece3;
            padding: 55px;
            text-align: center;
        }

        .empty-state h2 {
            font-size: 28px;
            margin-bottom: 12px;
        }

        .empty-state p {
            color: #74776e;
            margin-bottom: 25px;
        }

        @media (max-width: 700px) {
            .course-grid {
                grid-template-columns: 1fr;
            }

            .courses-page {
                margin-top: 40px;
            }

            h1 {
                letter-spacing: -2px;
            }

            .logout-btn {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <nav class="lubi-nav">

        <a href="index.php" class="lubi-logo">
            LUBI<span>.</span>
        </a>

        <div class="lubi-links">
            <a href="index.php">Home</a>
            <a href="courses.php">Courses</a>
            <a href="saved.php">Saved Posts</a>
            <a href="profile.php">My Profile</a>

            <?php if ($_SESSION['userType'] === 'admin') { ?>
                <a href="admin.php">Admin Panel</a>
            <?php } ?>
        </div>

        <a href="logout.php" class="logout-btn">
            Logout
        </a>

    </nav>

    <main class="courses-page">

        <a href="index.php" class="back-link">
            ← Back to Home
        </a>

        <p class="eyebrow">LUBI / LEARN & GROW</p>

        <h1>Explore <em>Courses.</em></h1>

        <p class="subtitle">
            Discover beauty courses, develop your skills,
            and learn from professionals.
        </p>

        <div class="course-actions">

            <a href="myCourses.php" class="btn btn-light">
                My Courses
            </a>

            <?php if (
                $_SESSION['userType'] === 'professional'
                || $_SESSION['userType'] === 'admin'
            ) { ?>

                <a href="createCourse.php" class="btn">
                    + Create Course
                </a>

            <?php } ?>

        </div>

        <?php if (!empty($courses)) { ?>

            <div class="course-grid">

                <?php foreach ($courses as $course) { ?>

                    <article class="course-card">

                        <span class="course-category">
                            <?php echo htmlspecialchars(
                                $course['categoryName'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </span>

                        <h2>
                            <?php echo htmlspecialchars(
                                $course['title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </h2>

                        <p class="professional">
                            By
                            <?php echo htmlspecialchars(
                                $course['professionalName'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </p>

                        <p class="description">
                            <?php echo nl2br(htmlspecialchars(
                                $course['description'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            )); ?>
                        </p>

                        <div class="card-footer">

                            <a
                                href="course.php?courseID=<?php echo (int)$course['courseID']; ?>"
                                class="btn"
                            >
                                View Course
                            </a>

                        </div>

                    </article>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty-state">

                <h2>No courses available yet.</h2>

                <p>
                    New beauty courses will appear here
                    when professionals create them.
                </p>

                <?php if (
                    $_SESSION['userType'] === 'professional'
                    || $_SESSION['userType'] === 'admin'
                ) { ?>

                    <a href="createCourse.php" class="btn">
                        Create Your First Course
                    </a>

                <?php } ?>

            </div>

        <?php } ?>

    </main>

</body>
</html>

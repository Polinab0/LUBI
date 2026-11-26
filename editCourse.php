<?php

require_once "includes/session.php";
confirm_logged_in();

require_once "dbcon.php";
require_once "classes/Course.php";

// Connect to database
$database = new Database();
$dbCon = $database->connect();

$courseObject = new Course($dbCon);

// Get course ID
$courseID = filter_input(
    INPUT_GET,
    'courseID',
    FILTER_VALIDATE_INT
);

if (!$courseID) {
    header("Location: courses.php");
    exit();
}

// Get course
$course = $courseObject->getCourseById($courseID);

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Only course owner or admin can edit
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

// Update course
if (isset($_POST['updateCourse'])) {

    // Check CSRF token
    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {
        die("Invalid CSRF token.");
    }

    // Get form values
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $categoryID = filter_input(
        INPUT_POST,
        'categoryID',
        FILTER_VALIDATE_INT
    );

    // Validate title
    if (empty($title)) {
        die("Title is required.");
    }

    if (strlen($title) > 150) {
        die("Title is too long.");
    }

    // Validate description
    if (empty($description)) {
        die("Description is required.");
    }

    // Validate category
    if ($categoryID === false || $categoryID === null) {
        die("Invalid category.");
    }

    // Check category exists
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

    // Update course in database
    $courseObject->updateCourse(
        $courseID,
        $title,
        $description,
        $categoryID
    );

    // Redirect to course details
    header("Location: course.php?courseID=" . $courseID);
    exit();
}

// Escape text safely
function editCourseText($value) {
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

    <title>LUBI — Edit Course</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        .edit-course-page {
            max-width: 850px;
            margin: 70px auto 110px;
            padding: 0 24px;
        }

        .edit-course-back {
            display: inline-block;
            margin-bottom: 35px;
            color: #51564b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .edit-course-back:hover {
            text-decoration: underline;
        }

        .edit-course-heading {
            margin-bottom: 35px;
        }

        .edit-course-heading h1 {
            font-size: clamp(38px, 6vw, 60px);
            line-height: 1.15;
            margin: 12px 0;
            color: #22251f;
        }

        .edit-course-heading h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .edit-course-heading p {
            color: #777a70;
            font-size: 15px;
            line-height: 1.7;
        }

        .edit-course-card {
            background: #ffffff;
            border: 1px solid #e5e6df;
            padding: 40px;
        }

        .edit-course-card h2 {
            margin: 0 0 30px;
            font-size: 24px;
            color: #252820;
        }

        .edit-course-field {
            margin-bottom: 24px;
        }

        .edit-course-field label {
            display: block;
            margin-bottom: 9px;
            color: #343a30;
            font-size: 13px;
            font-weight: 700;
        }

        .edit-course-field input,
        .edit-course-field textarea,
        .edit-course-field select {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 14px 16px;
            background: #fafbf8;
            border: 1px solid #d9ded2;
            border-radius: 0;
            color: #252820;
            font: inherit;
            font-size: 14px;
        }

        .edit-course-field input:focus,
        .edit-course-field textarea:focus,
        .edit-course-field select:focus {
            outline: 2px solid #a6b795;
            outline-offset: 1px;
        }

        .edit-course-field textarea {
            min-height: 180px;
            resize: vertical;
            line-height: 1.7;
        }

        .edit-course-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            padding-top: 25px;
            border-top: 1px solid #e8e9e3;
        }

        .edit-course-submit {
            padding: 14px 26px;
            background: #242720;
            border: 1px solid #242720;
            color: #ffffff;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .edit-course-submit:hover {
            background: #41483a;
        }

        .edit-course-cancel {
            display: inline-block;
            padding: 14px 26px;
            background: #e9eddf;
            border: 1px solid #d6dbcb;
            color: #242820;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .edit-course-cancel:hover {
            background: #d8e1ca;
        }

        @media (max-width: 600px) {
            .edit-course-page {
                margin-top: 40px;
                padding: 0 16px;
            }

            .edit-course-card {
                padding: 24px;
            }

            .edit-course-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .edit-course-submit,
            .edit-course-cancel {
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

    <main class="edit-course-page">

        <a
            href="course.php?courseID=<?php echo (int) $courseID; ?>"
            class="edit-course-back"
        >
            ← Back to Course
        </a>

        <!-- Page heading -->
        <div class="edit-course-heading">

            <span class="eyebrow">
                LUBI / COURSE MANAGEMENT
            </span>

            <h1>
                Edit <em>Course.</em>
            </h1>

            <p>
                Update your course information and keep
                your learning content up to date.
            </p>

        </div>

        <!-- Edit Course form -->
        <section class="edit-course-card">

            <h2>Course Information</h2>

            <form method="post">

                <!-- CSRF protection -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo editCourseText(csrf_token()); ?>"
                >

                <!-- Title -->
                <div class="edit-course-field">

                    <label for="title">
                        Course Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        id="title"
                        maxlength="150"
                        value="<?php echo editCourseText($course['title']); ?>"
                        required
                    >

                </div>

                <!-- Description -->
                <div class="edit-course-field">

                    <label for="description">
                        Course Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        required
                    ><?php echo editCourseText($course['description']); ?></textarea>

                </div>

                <!-- Category -->
                <div class="edit-course-field">

                    <label for="categoryID">
                        Category
                    </label>

                    <select
                        name="categoryID"
                        id="categoryID"
                        required
                    >

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?php echo (int) $category['categoryID']; ?>"
                                <?php
                                if ($category['name'] === $course['categoryName']) {
                                    echo 'selected';
                                }
                                ?>
                            >
                                <?php echo editCourseText($category['name']); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Buttons -->
                <div class="edit-course-actions">

                    <button
                        type="submit"
                        name="updateCourse"
                        class="edit-course-submit"
                    >
                        Save Changes
                    </button>

                    <a
                        href="course.php?courseID=<?php echo (int) $courseID; ?>"
                        class="edit-course-cancel"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </section>

    </main>

</body>

</html>

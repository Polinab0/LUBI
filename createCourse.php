<?php

// Include session functions
require_once "includes/session.php";

// Check if user is logged in
confirm_logged_in();

// Include database and Course class
require_once "dbcon.php";
require_once "classes/Course.php";

// Connect to database
$database = new Database();
$dbCon = $database->connect();

// Create Course object
$courseObject = new Course($dbCon);

// Only professionals and admins can create courses
if (
    $_SESSION['userType'] !== 'professional' &&
    $_SESSION['userType'] !== 'admin'
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

// Handle course creation
if (isset($_POST['submit'])) {

    // Check CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
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

    // Check that category exists
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

    // Logged in user becomes course owner
    $professionalID = $_SESSION['userID'];

    // Create course
    $courseID = $courseObject->createCourse(
        $title,
        $description,
        $professionalID,
        $categoryID
    );

    // Redirect to course page
    header("Location: course.php?courseID=" . $courseID);
    exit();
}

// Escape text safely
function createCourseText($value) {
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

    <title>LUBI — Create Course</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        .create-course-page {
            max-width: 850px;
            margin: 70px auto 110px;
            padding: 0 24px;
        }

        .create-course-back {
            display: inline-block;
            margin-bottom: 35px;
            color: #51564b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .create-course-back:hover {
            text-decoration: underline;
        }

        .create-course-heading {
            margin-bottom: 35px;
        }

        .create-course-heading h1 {
            font-size: clamp(38px, 6vw, 60px);
            line-height: 1.15;
            margin: 12px 0;
            color: #22251f;
        }

        .create-course-heading h1 em {
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .create-course-heading p {
            color: #777a70;
            font-size: 15px;
            line-height: 1.7;
        }

        .create-course-card {
            background: #ffffff;
            border: 1px solid #e5e6df;
            padding: 40px;
        }

        .create-course-card h2 {
            margin: 0 0 30px;
            font-size: 24px;
            color: #252820;
        }

        .create-course-field {
            margin-bottom: 24px;
        }

        .create-course-field label {
            display: block;
            margin-bottom: 9px;
            color: #343a30;
            font-size: 13px;
            font-weight: 700;
        }

        .create-course-field input,
        .create-course-field textarea,
        .create-course-field select {
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

        .create-course-field input:focus,
        .create-course-field textarea:focus,
        .create-course-field select:focus {
            outline: 2px solid #a6b795;
            outline-offset: 1px;
        }

        .create-course-field textarea {
            min-height: 180px;
            resize: vertical;
            line-height: 1.7;
        }

        .create-course-hint {
            margin-top: 8px;
            font-size: 12px;
            color: #85897f;
        }

        .create-course-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            padding-top: 25px;
            border-top: 1px solid #e8e9e3;
        }

        .create-course-submit {
            padding: 14px 26px;
            background: #242720;
            border: 1px solid #242720;
            color: #ffffff;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .create-course-submit:hover {
            background: #41483a;
        }

        .create-course-cancel {
            display: inline-block;
            padding: 14px 26px;
            background: #e9eddf;
            border: 1px solid #d6dbcb;
            color: #242820;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .create-course-cancel:hover {
            background: #d8e1ca;
        }

        @media (max-width: 600px) {
            .create-course-page {
                margin-top: 40px;
                padding: 0 16px;
            }

            .create-course-card {
                padding: 24px;
            }

            .create-course-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .create-course-submit,
            .create-course-cancel {
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

    <main class="create-course-page">

        <a href="courses.php" class="create-course-back">
            ← Back to Courses
        </a>

        <!-- Page heading -->
        <div class="create-course-heading">

            <span class="eyebrow">
                LUBI / LEARN & GROW
            </span>

            <h1>
                Create <em>Course.</em>
            </h1>

            <p>
                Share your expertise and help others
                develop their beauty skills.
            </p>

        </div>

        <!-- Create Course form -->
        <section class="create-course-card">

            <h2>Course Information</h2>

            <form
                action="createCourse.php"
                method="post"
            >

                <!-- CSRF protection -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo createCourseText(csrf_token()); ?>"
                >

                <!-- Title -->
                <div class="create-course-field">

                    <label for="title">
                        Course Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        id="title"
                        maxlength="150"
                        placeholder="e.g. Beginner Makeup Course"
                        required
                    >

                </div>

                <!-- Description -->
                <div class="create-course-field">

                    <label for="description">
                        Course Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        placeholder="Describe what learners will learn..."
                        required
                    ></textarea>

                </div>

                <!-- Category -->
                <div class="create-course-field">

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
                            >
                                <?php echo createCourseText($category['name']); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Buttons -->
                <div class="create-course-actions">

                    <button
                        type="submit"
                        name="submit"
                        class="create-course-submit"
                    >
                        Create Course
                    </button>

                    <a
                        href="courses.php"
                        class="create-course-cancel"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </section>

    </main>

</body>

</html>

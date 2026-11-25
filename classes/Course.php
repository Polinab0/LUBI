<?php

class Course {

    private $dbCon;

    // Store the database connection
    public function __construct($dbCon) {
        $this->dbCon = $dbCon;
    }

    // Get all courses
    public function getAllCourses() {

        $query = $this->dbCon->prepare(
            "SELECT
                course.courseID,
                course.title,
                course.description,
                course.createdAt,
                course.professionalID,
                category.name AS categoryName,
                user.username AS professionalName
            FROM course
            INNER JOIN category
                ON course.categoryID = category.categoryID
            INNER JOIN user
                ON course.professionalID = user.userID
            ORDER BY course.createdAt DESC"
        );

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get one course
    public function getCourseById($courseID) {

        $query = $this->dbCon->prepare(
            "SELECT
                course.courseID,
                course.title,
                course.description,
                course.createdAt,
                course.professionalID,
                category.name AS categoryName,
                user.username AS professionalName
            FROM course
            INNER JOIN category
                ON course.categoryID = category.categoryID
            INNER JOIN user
                ON course.professionalID = user.userID
            WHERE course.courseID = :courseID"
        );

        $query->bindParam(
            ':courseID',
            $courseID,
            PDO::PARAM_INT
        );

        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new course
    public function createCourse(
        $title,
        $description,
        $professionalID,
        $categoryID
    ) {

        $query = $this->dbCon->prepare(
            "INSERT INTO course (
                title,
                description,
                professionalID,
                categoryID
            )
            VALUES (
                :title,
                :description,
                :professionalID,
                :categoryID
            )"
        );

        $query->execute([
            ':title' => $title,
            ':description' => $description,
            ':professionalID' => $professionalID,
            ':categoryID' => $categoryID
        ]);

        return $this->dbCon->lastInsertId();
    }



    // Enroll a user in a course
    public function enrollUser($userID, $courseID) {

       $query = $this->dbCon->prepare(
          "INSERT INTO course_enrollment (
              userID,
              courseID
        )
           VALUES (
              :userID,
              :courseID
        )"
    );

         $query->execute([
            ':userID' => $userID,
            ':courseID' => $courseID
        ]);
   }


   // Check if a user is already enrolled
public function isUserEnrolled($userID, $courseID) {

       $query = $this->dbCon->prepare(
           "SELECT enrollmentID
            FROM course_enrollment
            WHERE userID = :userID
            AND courseID = :courseID"
       );

       $query->execute([
           ':userID' => $userID,
           ':courseID' => $courseID
       ]);

       return $query->fetch(PDO::FETCH_ASSOC);
   }


      // Get courses the user is enrolled in
    public function getCoursesByUser($userID) {

    $query = $this->dbCon->prepare(
        "SELECT
            course.courseID,
            course.title,
            course.description,
            course.createdAt,
            course.professionalID,
            category.name AS categoryName,
            user.username AS professionalName
        FROM course_enrollment
        INNER JOIN course
            ON course_enrollment.courseID = course.courseID
        INNER JOIN category
            ON course.categoryID = category.categoryID
        INNER JOIN user
            ON course.professionalID = user.userID
        WHERE course_enrollment.userID = :userID
        ORDER BY course_enrollment.enrolledAt DESC"
    );

    $query->execute([
        ':userID' => $userID
    ]);

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Remove a user from a course
public function unenrollUser($userID, $courseID) {

    $query = $this->dbCon->prepare(
        "DELETE FROM course_enrollment
         WHERE userID = :userID
         AND courseID = :courseID"
    );

    $query->execute([
        ':userID' => $userID,
        ':courseID' => $courseID
    ]);
}

// Delete a course
public function deleteCourse($courseID) {

    $this->dbCon->beginTransaction();

    try {

        $enrollmentQuery = $this->dbCon->prepare(
            "DELETE FROM course_enrollment
             WHERE courseID = :courseID"
        );

        $enrollmentQuery->execute([
            ':courseID' => $courseID
        ]);

        $courseQuery = $this->dbCon->prepare(
            "DELETE FROM course
             WHERE courseID = :courseID"
        );

        $courseQuery->execute([
            ':courseID' => $courseID
        ]);

        $this->dbCon->commit();

    } catch (Exception $e) {

        $this->dbCon->rollBack();

        throw $e;
    }
}


// Update a course
public function updateCourse(
    $courseID,
    $title,
    $description,
    $categoryID
) {

    $query = $this->dbCon->prepare(
        "UPDATE course
         SET
            title = :title,
            description = :description,
            categoryID = :categoryID
         WHERE courseID = :courseID"
    );

    $query->execute([
        ':title' => $title,
        ':description' => $description,
        ':categoryID' => $categoryID,
        ':courseID' => $courseID
    ]);
}

}
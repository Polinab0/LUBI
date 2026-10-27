<?php

class Comment {

    private $dbCon;

    // Save the database connection inside the class
    public function __construct($dbCon) {
        $this->dbCon = $dbCon;
    }

    // Add a new comment
    public function addComment(
        $userID,
        $postID,
        $text
    ) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "INSERT INTO comment
            (text, userID, postID)
            VALUES
            (:text, :userID, :postID)"
        );

        // Bind the values
        $query->bindParam(':text', $text);
        $query->bindParam(':userID', $userID);
        $query->bindParam(':postID', $postID);

        // Execute the query
        return $query->execute();
    }


    // Get all comments for one post
    public function getComments($postID) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "SELECT
                comment.commentID,
                comment.text,
                comment.createdAt,
                user.username
            FROM comment
            JOIN user
                ON comment.userID = user.userID
            WHERE comment.postID = :postID
            ORDER BY comment.createdAt DESC"
        );

        // Bind the post ID
        $query->bindParam(':postID', $postID);

        // Execute the query
        $query->execute();

        // Return all comments
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // Get the number of comments for one post
    public function getCommentCount($postID) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "SELECT COUNT(*) AS commentCount
            FROM comment
            WHERE postID = :postID"
        );

        // Bind the post ID
        $query->bindParam(':postID', $postID);

        // Execute the query
        $query->execute();

        // Get the result
        $result = $query->fetch(PDO::FETCH_ASSOC);

        // Return the number of comments
        return $result['commentCount'];
    }
// Get the latest comments
public function getLatestComments($limit = 5) {

    // Prepare the SQL query
    $query = $this->dbCon->prepare(
        "SELECT
            comment.commentID,
            comment.text,
            comment.createdAt,
            comment.postID,
            user.username
        FROM comment
        JOIN user
            ON comment.userID = user.userID
        ORDER BY comment.createdAt DESC
        LIMIT :limit"
    );

    // Bind the limit as an integer
    $query->bindValue(
        ':limit',
        $limit,
        PDO::PARAM_INT
    );

    // Execute the query
    $query->execute();

    // Return the latest comments
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

}
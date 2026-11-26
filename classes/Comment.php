<?php

class Comment {

    private $dbCon;

    // Save the database connection inside the class
    public function __construct($dbCon) {
        $this->dbCon = $dbCon;
    }

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

        $query->bindParam(':text', $text);
        $query->bindParam(':userID', $userID);
        $query->bindParam(':postID', $postID);

        // Execute the query
        return $query->execute();
    }


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

        $query->bindParam(':postID', $postID);

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // Get the number of comments for one post
    public function getCommentCount($postID) {

        $query = $this->dbCon->prepare(
            "SELECT COUNT(*) AS commentCount
            FROM comment
            WHERE postID = :postID"
        );

        $query->bindParam(':postID', $postID);

        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['commentCount'];
    }
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

    $query->execute();

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

}
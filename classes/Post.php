<?php

class Post {

    private $dbCon;

    // Save the database connection inside the class
    public function __construct($dbCon) {
        $this->dbCon = $dbCon;
    }


    // Create a new post
    public function createPost(
        $userID,
        $categoryID,
        $caption
    ) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "INSERT INTO post
            (userID, categoryID, caption)
            VALUES
            (:userID, :categoryID, :caption)"
        );

        // Bind the values to the SQL placeholders
        $query->bindParam(':userID', $userID);
        $query->bindParam(':categoryID', $categoryID);
        $query->bindParam(':caption', $caption);

        // Execute the query
        $query->execute();

        // Return the ID of the new post
        return $this->dbCon->lastInsertId();
    }


    // Add media to a post
    public function addMedia(
        $postID,
        $file,
        $type
    ) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "INSERT INTO media
            (file, type, postID)
            VALUES
            (:file, :type, :postID)"
        );

        // Bind the values to the SQL placeholders
        $query->bindParam(':file', $file);
        $query->bindParam(':type', $type);
        $query->bindParam(':postID', $postID);

        // Execute the query
        return $query->execute();
    }


    // Get all posts with user, category and media
    public function getAllPosts() {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "SELECT
                post.postID,
                post.caption,
                post.createdAt,
                user.username,
                category.name AS categoryName,
                media.file,
                media.type
            FROM post
            JOIN user
                ON post.userID = user.userID
            JOIN category
                ON post.categoryID = category.categoryID
            LEFT JOIN media
                ON post.postID = media.postID
            ORDER BY post.createdAt DESC"
        );

        // Execute the query
        $query->execute();

        // Return all posts
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // Get one post by ID
    public function getPostById($postID) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "SELECT
                post.postID,
                post.caption,
                post.createdAt,
                user.username,
                category.name AS categoryName,
                media.file,
                media.type
            FROM post
            JOIN user
                ON post.userID = user.userID
            JOIN category
                ON post.categoryID = category.categoryID
            LEFT JOIN media
                ON post.postID = media.postID
            WHERE post.postID = :postID"
        );

        // Bind the post ID
        $query->bindParam(':postID', $postID);

        // Execute the query
        $query->execute();

        // Return one post
        return $query->fetch(PDO::FETCH_ASSOC);
    }


    // Add a like to a post
    public function likePost(
        $userID,
        $postID
    ) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "INSERT IGNORE INTO post_like
            (userID, postID)
            VALUES
            (:userID, :postID)"
        );

        // Bind the values
        $query->bindParam(':userID', $userID);
        $query->bindParam(':postID', $postID);

        // Execute the query
        return $query->execute();
    }


    // Get the number of likes for a post
    public function getLikeCount($postID) {

        // Prepare the SQL query
        $query = $this->dbCon->prepare(
            "SELECT COUNT(*) AS likeCount
            FROM post_like
            WHERE postID = :postID"
        );

        // Bind the post ID
        $query->bindParam(':postID', $postID);

        // Execute the query
        $query->execute();

        // Get the result
        $result = $query->fetch(PDO::FETCH_ASSOC);

        // Return the number of likes
        return $result['likeCount'];
    }

// Get posts by category
public function getPostsByCategory($categoryID) {

    // Prepare the SQL query
    $query = $this->dbCon->prepare(
        "SELECT
            post.postID,
            post.caption,
            post.createdAt,
            user.username,
            category.name AS categoryName,
            media.file,
            media.type
        FROM post
        JOIN user
            ON post.userID = user.userID
        JOIN category
            ON post.categoryID = category.categoryID
        LEFT JOIN media
            ON post.postID = media.postID
        WHERE post.categoryID = :categoryID
        ORDER BY post.createdAt DESC"
    );

    // Bind the category ID
    $query->bindParam(':categoryID', $categoryID);

    // Execute the query
    $query->execute();

    // Return filtered posts
    return $query->fetchAll(PDO::FETCH_ASSOC);
}


}

<?php

class Post {

    private $dbCon;

    // Save the database connection inside the class
    public function __construct($dbCon) {
        $this->dbCon = $dbCon;
    }

    public function createPost(
        $userID,
        $categoryID,
        $caption
    ) {

        $query = $this->dbCon->prepare(
            "INSERT INTO post
            (userID, categoryID, caption)
            VALUES
            (:userID, :categoryID, :caption)"
        );

        $query->bindParam(':userID', $userID);
        $query->bindParam(':categoryID', $categoryID);
        $query->bindParam(':caption', $caption);


        $query->execute();

        // Return the ID of the new post
        return $this->dbCon->lastInsertId();
    }

    public function addMedia(
        $postID,
        $file,
        $type
    ) {

        $query = $this->dbCon->prepare(
            "INSERT INTO media
            (file, type, postID)
            VALUES
            (:file, :type, :postID)"
        );

        $query->bindParam(':file', $file);
        $query->bindParam(':type', $type);
        $query->bindParam(':postID', $postID);

        return $query->execute();
    }


    // Get all posts with user, category and media
    public function getAllPosts() {

        $query = $this->dbCon->prepare(
            "SELECT
                post.postID,
                post.userID,
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

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // Get one post by ID
    public function getPostById($postID) {

        $query = $this->dbCon->prepare(
            "SELECT
                post.postID,
                post.userID,
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

        $query->bindParam(':postID', $postID);

        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }


    public function likePost(
        $userID,
        $postID
    ) {

        $query = $this->dbCon->prepare(
            "INSERT IGNORE INTO post_like
            (userID, postID)
            VALUES
            (:userID, :postID)"
        );

        // Bind the values
        $query->bindParam(':userID', $userID);
        $query->bindParam(':postID', $postID);

        return $query->execute();
    }


    // Get the number of likes for a post
    public function getLikeCount($postID) {

        $query = $this->dbCon->prepare(
            "SELECT COUNT(*) AS likeCount
            FROM post_like
            WHERE postID = :postID"
        );

        $query->bindParam(':postID', $postID);

        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['likeCount'];
    }

// Get posts by category
public function getPostsByCategory($categoryID) {

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

    $query->bindParam(':categoryID', $categoryID);

    $query->execute();

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

public function getNewestPosts($limit = 4) {

    $query = $this->dbCon->prepare(
        "SELECT
            post.postID,
            post.userID,
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
        ORDER BY post.createdAt DESC
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

public function deletePost($postID) {

    try {

        $this->dbCon->beginTransaction();

        // Delete saved posts
        $query = $this->dbCon->prepare(
            "DELETE FROM saved_post
            WHERE postID = :postID"
        );
        $query->bindParam(':postID', $postID);
        $query->execute();

        // Delete likes
        $query = $this->dbCon->prepare(
            "DELETE FROM post_like
            WHERE postID = :postID"
        );
        $query->bindParam(':postID', $postID);
        $query->execute();

        // Delete comments
        $query = $this->dbCon->prepare(
            "DELETE FROM comment
            WHERE postID = :postID"
        );
        $query->bindParam(':postID', $postID);
        $query->execute();

        // Delete media
        $query = $this->dbCon->prepare(
            "DELETE FROM media
            WHERE postID = :postID"
        );
        $query->bindParam(':postID', $postID);
        $query->execute();

        // Delete the post
        $query = $this->dbCon->prepare(
            "DELETE FROM post
            WHERE postID = :postID"
        );
        $query->bindParam(':postID', $postID);
        $query->execute();

        $this->dbCon->commit();

        return true;

    } catch (Exception $error) {

        // Undo all changes if something fails
        if ($this->dbCon->inTransaction()) {
            $this->dbCon->rollBack();
        }

        return false;
    }
}


}

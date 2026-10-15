-- Create the LUBI database
CREATE DATABASE IF NOT EXISTS LUBI;

-- Select the LUBI database
USE LUBI;


-- Create the user table
CREATE TABLE user (
    userID INT NOT NULL AUTO_INCREMENT,
    userType VARCHAR(50) NOT NULL,
    username VARCHAR(80) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    city VARCHAR(100),
    bio VARCHAR(255),

    PRIMARY KEY (userID)
) ENGINE=InnoDB;


-- Create the category table
CREATE TABLE category (
    categoryID INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,

    PRIMARY KEY (categoryID)
) ENGINE=InnoDB;


-- Create the post table
CREATE TABLE post (
    postID INT NOT NULL AUTO_INCREMENT,
    caption VARCHAR(255),
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    userID INT NOT NULL,
    categoryID INT NOT NULL,

    PRIMARY KEY (postID),

    FOREIGN KEY (userID)
        REFERENCES user(userID),

    FOREIGN KEY (categoryID)
        REFERENCES category(categoryID)
) ENGINE=InnoDB;


-- Create the media table
CREATE TABLE media (
    mediaID INT NOT NULL AUTO_INCREMENT,
    file VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    postID INT NOT NULL,

    PRIMARY KEY (mediaID),

    FOREIGN KEY (postID)
        REFERENCES post(postID)
) ENGINE=InnoDB;


-- Create the comment table
CREATE TABLE comment (
    commentID INT NOT NULL AUTO_INCREMENT,
    text VARCHAR(255) NOT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    userID INT NOT NULL,
    postID INT NOT NULL,

    PRIMARY KEY (commentID),

    FOREIGN KEY (userID)
        REFERENCES user(userID),

    FOREIGN KEY (postID)
        REFERENCES post(postID)
) ENGINE=InnoDB;


-- Create the post_like table
CREATE TABLE post_like (
    userID INT NOT NULL,
    postID INT NOT NULL,

    PRIMARY KEY (userID, postID),

    FOREIGN KEY (userID)
        REFERENCES user(userID),

    FOREIGN KEY (postID)
        REFERENCES post(postID)
) ENGINE=InnoDB;


-- Create the saved_post table
CREATE TABLE saved_post (
    userID INT NOT NULL,
    postID INT NOT NULL,
    savedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (userID, postID),

    FOREIGN KEY (userID)
        REFERENCES user(userID),

    FOREIGN KEY (postID)
        REFERENCES post(postID)
) ENGINE=InnoDB;


-- Create the follow table
CREATE TABLE follow (
    followerID INT NOT NULL,
    followingID INT NOT NULL,

    PRIMARY KEY (followerID, followingID),

    FOREIGN KEY (followerID)
        REFERENCES user(userID),

    FOREIGN KEY (followingID)
        REFERENCES user(userID)
) ENGINE=InnoDB;


-- Insert default categories
INSERT INTO category (name)
VALUES
('Makeup'),
('Nails'),
('Hair'),
('Brows & Lashes'),
('Skincare');
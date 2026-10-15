<?php

// Start the PHP session
session_start();

// Check if the user is logged in
function logged_in() {

    return isset($_SESSION['userID']);
}

// Protect pages that require login
function confirm_logged_in() {

    if (!logged_in()) {

        // Redirect to login page
        header("Location: login.php");
        exit();
    }
}
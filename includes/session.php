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
// Create a CSRF token
function csrf_token() {

    if (!isset($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}


// Check if the CSRF token is valid
function verify_csrf_token($token) {

    return isset($_SESSION['csrf_token'])
        && hash_equals(
            $_SESSION['csrf_token'],
            $token
        );
}
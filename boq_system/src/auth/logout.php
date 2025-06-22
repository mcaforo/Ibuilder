<?php
// src/auth/logout.php

// Ensure config.php is loaded to initialize session handling if not already done.
if (file_exists(__DIR__ . '/../core/config.php')) {
    require_once __DIR__ . '/../core/config.php';
} else {
    // Fallback if session isn't already started by config.php or similar
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to login page with a success message (optional)
// To set a message, you'd need to start a new session briefly, or pass via GET.
// For simplicity, just redirect.
// If login_form.php starts session, it can display this.
if (session_status() == PHP_SESSION_NONE) { // Start a new session to pass a message
    session_start();
}
$_SESSION['success_message'] = "You have been successfully logged out.";
header('Location: ../../templates/login_form.php');
exit;
?>

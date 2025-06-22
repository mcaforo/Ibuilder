<?php
// src/auth/login.php

require_once __DIR__ . '/../core/Database.php';
// Ensure config.php (which starts session) is loaded.
if (file_exists(__DIR__ . '/../core/config.php')) {
    require_once __DIR__ . '/../core/config.php';
} else {
    die("Critical error: config.php not found from login.php.");
}

// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../templates/login_form.php');
    exit;
}

// Get form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// --- Basic Validation ---
$errors = [];

if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode("<br>", $errors);
    header('Location: ../../templates/login_form.php');
    exit;
}

// --- Attempt to log in user ---
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start session and store user data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in_at'] = time();

            // Regenerate session ID for security
            session_regenerate_id(true);

            $_SESSION['success_message'] = "Login successful! Welcome, " . htmlspecialchars($user['name']) . ".";

            // Redirect to a dashboard or main page
            // For now, let's redirect to public/index.php which will handle routing
            header('Location: ../../public/index.php');
            exit;
        } else {
            // Invalid password
            $_SESSION['error_message'] = "Invalid email or password.";
            header('Location: ../../templates/login_form.php');
            exit;
        }
    } else {
        // No user found with that email
        $_SESSION['error_message'] = "Invalid email or password.";
        header('Location: ../../templates/login_form.php');
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error during login. Please try again later.";
    error_log("PDOException in login.php: " . $e->getMessage());
    header('Location: ../../templates/login_form.php');
    exit;
} catch (Exception $e) {
    $_SESSION['error_message'] = "An unexpected error occurred. Please try again later.";
    error_log("General Exception in login.php: " . $e->getMessage());
    header('Location: ../../templates/login_form.php');
    exit;
}

?>

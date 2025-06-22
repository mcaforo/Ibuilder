<?php
// src/auth/register.php

// Ensure core files are loaded.
// The Database class will handle its own config loading.
require_once __DIR__ . '/../core/Database.php';
// Config is needed for session_start and other constants if not handled by Database.php including it.
// Let's ensure config.php (which starts session) is loaded once.
if (file_exists(__DIR__ . '/../core/config.php')) {
    require_once __DIR__ . '/../core/config.php';
} else {
    die("Critical error: config.php not found from register.php.");
}


// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../templates/register_form.php');
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'Registered User'; // Default to 'Registered User'

// --- Basic Validation ---
$errors = [];

if (empty($name)) {
    $errors[] = "Full name is required.";
}

if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

if (!in_array($role, ['Admin', 'Registered User'])) {
    $errors[] = "Invalid role selected.";
    // In a real app, admin role assignment would be more controlled.
}


// If validation errors, redirect back to form with errors
if (!empty($errors)) {
    $_SESSION['error_message'] = implode("<br>", $errors);
    // Store submitted values to repopulate form (optional, good UX)
    $_SESSION['form_data'] = ['name' => $name, 'email' => $email, 'role' => $role];
    header('Location: ../../templates/register_form.php');
    exit;
}

// --- Check if email already exists ---
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "An account with this email already exists. Please <a href='../../templates/login_form.php'>login</a> or use a different email.";
        $_SESSION['form_data'] = ['name' => $name, 'email' => $email, 'role' => $role];
        header('Location: ../../templates/register_form.php');
        exit;
    }

    // --- Hash password ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if ($hashed_password === false) {
        // Handle password_hash failure (rare)
        $_SESSION['error_message'] = "Could not process password. Please try again.";
        error_log("password_hash() failed for user: " . $email);
        header('Location: ../../templates/register_form.php');
        exit;
    }


    // --- Insert user into database ---
    $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
    $insert_stmt->bindParam(':name', $name);
    $insert_stmt->bindParam(':email', $email);
    $insert_stmt->bindParam(':password', $hashed_password);
    $insert_stmt->bindParam(':role', $role);

    if ($insert_stmt->execute()) {
        $_SESSION['success_message'] = "Registration successful! You can now login.";
        // Clear form data on success
        if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']);

        // Get the ID of the newly inserted user
        $user_id = $conn->lastInsertId();

        // Optional: Automatically log in the user after registration
        // $_SESSION['user_id'] = $user_id;
        // $_SESSION['user_name'] = $name;
        // $_SESSION['user_role'] = $role;
        // header('Location: ../../public/index.php'); // Or to a dashboard

        // For now, redirect to login page
        header('Location: ../../templates/login_form.php');
        exit;
    } else {
        $_SESSION['error_message'] = "Registration failed. Please try again later.";
        error_log("User registration failed for email: " . $email . ". PDO Error: " . implode(":", $insert_stmt->errorInfo()));
        header('Location: ../../templates/register_form.php');
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error during registration. Please try again later.";
    // Log the detailed error for the admin/developer
    error_log("PDOException in register.php: " . $e->getMessage());
    header('Location: ../../templates/register_form.php');
    exit;
} catch (Exception $e) {
    $_SESSION['error_message'] = "An unexpected error occurred. Please try again later.";
    error_log("General Exception in register.php: " . $e->getMessage());
    header('Location: ../../templates/register_form.php');
    exit;
}

?>

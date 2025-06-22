<?php
// src/modules/site_conditions/save_site_conditions.php

require_once __DIR__ . '/../../core/Database.php';
if (file_exists(__DIR__ . '/../../core/config.php')) {
    require_once __DIR__ . '/../../core/config.php'; // Ensures session_start()
} else {
    die("Critical error: config.php not found.");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to save site conditions.";
    // Adjust redirect to a relevant login page path
    header('Location: ../../../templates/login_form.php');
    exit;
}

// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If accessed directly, redirect to dashboard or project list
    header('Location: ../../../templates/dashboard.php'); // Adjust path
    exit;
}

// Get form data
$project_id = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
$soil_type = filter_input(INPUT_POST, 'soil_type', FILTER_SANITIZE_STRING);
$groundwater_table = filter_input(INPUT_POST, 'groundwater_table', FILTER_SANITIZE_STRING);
$terrain = filter_input(INPUT_POST, 'terrain', FILTER_SANITIZE_STRING);
$recommended_foundation = filter_input(INPUT_POST, 'recommended_foundation', FILTER_SANITIZE_STRING); // This is auto-suggested but saved

// --- Validation ---
$errors = [];

if (!$project_id) {
    $errors[] = "Invalid Project ID. Cannot save site conditions.";
} else {
    // Optional: Verify project_id belongs to the current user
    try {
        $db_check = Database::getInstance();
        $conn_check = $db_check->getConnection();
        $stmt_check = $conn_check->prepare("SELECT user_id FROM projects WHERE id = :project_id");
        $stmt_check->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt_check->execute();
        $project_owner = $stmt_check->fetchColumn();
        if ($project_owner === false) {
            $errors[] = "Project not found.";
        } elseif ($project_owner != $_SESSION['user_id']) {
            // This check is important if users should only edit their own projects' site conditions
            // For admins, you might allow this. Add role check if needed.
            // $errors[] = "You are not authorized to modify this project's site conditions.";
             $_SESSION['warning_message'] = "Warning: Modifying site conditions for a project potentially not owned by user (Admin override or shared project feature would handle this).";
        }
    } catch (PDOException $e) {
        error_log("Error checking project ownership: " . $e->getMessage());
        $errors[] = "Database error verifying project.";
    }
}


$valid_soil_types = ['Rocky', 'Lateritic', 'Clay', 'Loose Fill', 'Sand', 'Other', '']; // Allow empty if optional
if (!in_array($soil_type, $valid_soil_types)) {
    $errors[] = "Invalid soil type selected.";
}

$valid_groundwater = ['High', 'Low', 'Unknown', ''];
if (!in_array($groundwater_table, $valid_groundwater)) {
    $errors[] = "Invalid groundwater table option selected.";
}

$valid_terrain = ['Flat', 'Sloping', 'Terraced', 'Other', ''];
if (!in_array($terrain, $valid_terrain)) {
    $errors[] = "Invalid terrain option selected.";
}

if (strlen($recommended_foundation) > 255) {
    $errors[] = "Recommended foundation text is too long.";
}


// Redirect back to form with errors if any
$redirect_url = "../../../templates/site_condition_form.php?project_id=" . ($project_id ?: '');
if (!empty($errors)) {
    $_SESSION['error_message'] = implode("<br>", $errors);
    // Store submitted values to repopulate form (optional, good UX)
    $_SESSION['form_data_site_conditions'] = $_POST;
    header('Location: ' . $redirect_url);
    exit;
}

// --- Upsert (Insert or Update) into database ---
// The site_conditions table has a UNIQUE KEY on project_id, so we use ON DUPLICATE KEY UPDATE.
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $sql = "INSERT INTO site_conditions (project_id, soil_type, groundwater_table, terrain, recommended_foundation)
            VALUES (:project_id, :soil_type, :groundwater_table, :terrain, :recommended_foundation)
            ON DUPLICATE KEY UPDATE
                soil_type = VALUES(soil_type),
                groundwater_table = VALUES(groundwater_table),
                terrain = VALUES(terrain),
                recommended_foundation = VALUES(recommended_foundation),
                updated_at = CURRENT_TIMESTAMP";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindParam(':soil_type', $soil_type, PDO::PARAM_STR);
    $stmt->bindParam(':groundwater_table', $groundwater_table, PDO::PARAM_STR);
    $stmt->bindParam(':terrain', $terrain, PDO::PARAM_STR);
    $stmt->bindParam(':recommended_foundation', $recommended_foundation, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Site conditions for project ID " . htmlspecialchars($project_id) . " saved successfully!";
        if(isset($_SESSION['form_data_site_conditions'])) unset($_SESSION['form_data_site_conditions']);

        // Redirect back to the site conditions form (it will show the success message and updated data if reloaded)
        // Or to a project overview page
        header('Location: ' . $redirect_url);
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to save site conditions. Please try again.";
        error_log("Site conditions save failed for project ID {$project_id}. PDO Error: " . implode(":", $stmt->errorInfo()));
        $_SESSION['form_data_site_conditions'] = $_POST;
        header('Location: ' . $redirect_url);
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error during site conditions save. " . $e->getMessage();
    error_log("PDOException in save_site_conditions.php: " . $e->getMessage());
    $_SESSION['form_data_site_conditions'] = $_POST;
    header('Location: ' . $redirect_url);
    exit;
} catch (Exception $e) {
    $_SESSION['error_message'] = "An unexpected error occurred. " . $e->getMessage();
    error_log("General Exception in save_site_conditions.php: " . $e->getMessage());
    $_SESSION['form_data_site_conditions'] = $_POST;
    header('Location: ' . $redirect_url);
    exit;
}

?>

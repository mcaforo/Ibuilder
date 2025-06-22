<?php
<?php
// create_project.php

require_once __DIR__ . '/../../core/Database.php';
if (file_exists(__DIR__ . '/../../core/config.php')) {
    require_once __DIR__ . '/../../core/config.php'; // Ensures session_start()
} else {
    die("Critical error: config.php not found.");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to create a project.";
    header('Location: ../../../templates/login_form.php'); // Adjust path as needed
    exit;
}

$user_id = $_SESSION['user_id']; // Get user_id from session

// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If accessed directly, redirect to form or dashboard
    header('Location: ../../../templates/project_form.php'); // Adjust path as needed
    exit;
}

// Get form data and sanitize/validate
$project_name = trim(filter_input(INPUT_POST, 'project_name', FILTER_SANITIZE_STRING));
$region = trim(filter_input(INPUT_POST, 'region', FILTER_SANITIZE_STRING));
$location = trim(filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING));
$category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
$floors = filter_input(INPUT_POST, 'floors', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
$area = filter_input(INPUT_POST, 'area', FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]);

// --- Validation ---
$errors = [];
if (empty($project_name)) {
    $errors[] = "Project name is required.";
}
if (strlen($project_name) > 255) {
    $errors[] = "Project name is too long (max 255 characters).";
}

if (!empty($region) && strlen($region) > 100) {
    $errors[] = "Region name is too long (max 100 characters).";
}
if (!empty($location) && strlen($location) > 255) {
    $errors[] = "Location details are too long (max 255 characters).";
}

$valid_categories = ['Residential', 'Commercial', 'Institutional', 'Industrial'];
if (empty($category) || !in_array($category, $valid_categories)) {
    $errors[] = "Invalid building category selected.";
}

if ($floors === false || $floors === null) { // filter_validate_int returns false on failure, null if not set and flag not used
    $errors[] = "Number of floors must be a valid non-negative integer.";
}

if ($area !== null && $area === false) { // filter_validate_float returns false on failure
    $errors[] = "Total floor area must be a valid non-negative number.";
} elseif ($area !== null && $area > 99999999.99) { // Matches DECIMAL(10,2)
    $errors[] = "Total floor area is too large.";
}


if (!empty($errors)) {
    $_SESSION['error_message'] = implode("<br>", $errors);
    // Store submitted values to repopulate form (optional, good UX)
    $_SESSION['form_data_project'] = $_POST;
    header('Location: ../../../templates/project_form.php'); // Adjust path as needed
    exit;
}

// --- Insert into database ---
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $sql = "INSERT INTO projects (user_id, name, region, location, category, floors, area)
            VALUES (:user_id, :name, :region, :location, :category, :floors, :area)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $project_name, PDO::PARAM_STR);
    $stmt->bindParam(':region', $region, PDO::PARAM_STR);
    $stmt->bindParam(':location', $location, PDO::PARAM_STR);
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->bindParam(':floors', $floors, PDO::PARAM_INT);

    // Bind area, handling null if it was empty and valid
    if ($area === null || $area === '') { // Check for empty string too if field is optional and not filled
        $stmt->bindValue(':area', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':area', $area); // PDO will handle float type
    }


    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Project '" . htmlspecialchars($project_name) . "' created successfully!";
        $project_id = $conn->lastInsertId();
        // Clear form data on success
        if(isset($_SESSION['form_data_project'])) unset($_SESSION['form_data_project']);

        // Redirect to project view or dashboard, or perhaps site conditions form for this new project
        // For now, redirecting back to project form page (which will show the success message)
        // A better redirect would be to a page showing the project or a list of projects
        header('Location: ../../../templates/project_form.php?project_id=' . $project_id); // Adjust path
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to create project. Please try again.";
        error_log("Project creation failed for user ID {$user_id}. PDO Error: " . implode(":", $stmt->errorInfo()));
        $_SESSION['form_data_project'] = $_POST;
        header('Location: ../../../templates/project_form.php'); // Adjust path
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error during project creation. Please try again later.";
    error_log("PDOException in create_project.php: " . $e->getMessage());
    $_SESSION['form_data_project'] = $_POST;
    header('Location: ../../../templates/project_form.php'); // Adjust path
    exit;
} catch (Exception $e) {
    $_SESSION['error_message'] = "An unexpected error occurred. Please try again later.";
    error_log("General Exception in create_project.php: " . $e->getMessage());
    $_SESSION['form_data_project'] = $_POST;
    header('Location: ../../../templates/project_form.php'); // Adjust path
    exit;
}

?>
?>

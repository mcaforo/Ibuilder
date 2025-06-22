<?php
// public/index.php - Main Entry Point / Basic Router

// Ensure config is loaded to initialize session handling
if (file_exists(__DIR__ . '/../src/core/config.php')) {
    require_once __DIR__ . '/../src/core/config.php';
} else {
    // If config.php is not found, sessions might not work as expected.
    // For now, we'll attempt to start a session if it's not already started.
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // Potentially die with an error if config is critical and missing
    // die("Critical Error: Main configuration file missing.");
}

// Simple routing based on 'action' parameter or session status
$action = $_GET['action'] ?? null;

if (isset($_SESSION['user_id'])) {
    // User is logged in
    if ($action === 'logout') {
        header('Location: ../src/auth/logout.php');
        exit;
    } elseif ($action === 'create_project') {
        header('Location: ../templates/project_form.php');
        exit;
    } elseif ($action === 'site_conditions' && isset($_GET['project_id'])) {
        // Example: Redirect to site conditions form for a specific project
        // header('Location: ../templates/site_condition_form.php?project_id=' . $_GET['project_id']);
        // For now, let's just go to a placeholder or project form
        $_SESSION['info_message'] = "Site conditions module for project " . htmlspecialchars($_GET['project_id']) . " (to be implemented).";
        header('Location: ../templates/project_form.php'); // Placeholder redirect
        exit;
    }

    // Default for logged-in user: Redirect to a dashboard or project creation form
    // For now, let's create a simple dashboard placeholder if they are logged in and no other action.
    if (!file_exists('../templates/dashboard.php')) {
        // Create a very basic dashboard.php if it doesn't exist
        $dashboardContent = <<<'EOD'
<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header('Location: login_form.php'); // Should be handled by index.php ideally
    exit;
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<title>Dashboard - BOQ System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<style> body { padding: 20px; } .navbar { margin-bottom: 20px; }</style>
</head><body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">BOQ System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="../public/index.php?action=create_project">New Project</a></li>
        <!-- Add more links here: List Projects, Manage Materials etc. -->
        <li class="nav-item"><a class="nav-link" href="../public/index.php?action=logout">Logout (<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>)</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>!</h2>
    <p>This is your dashboard. From here you can manage your projects and BOQs.</p>
    <?php
    if(isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">'.htmlspecialchars($_SESSION['success_message']).'</div>';
        unset($_SESSION['success_message']);
    }
    if(isset($_SESSION['info_message'])) {
        echo '<div class="alert alert-info">'.htmlspecialchars($_SESSION['info_message']).'</div>';
        unset($_SESSION['info_message']);
    }
    ?>
    <p><a href="../public/index.php?action=create_project" class="btn btn-primary">Start a New Project</a></p>
    <!-- Later, list existing projects here -->
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
EOD;
        file_put_contents('../templates/dashboard.php', $dashboardContent);
    }
    header('Location: ../templates/dashboard.php');
    exit;

} else {
    // User is not logged in
    if ($action === 'register') {
        header('Location: ../templates/register_form.php');
        exit;
    }
    // Default for non-logged-in user: Redirect to login form
    // (Also handles if $action is null or any other value)
    header('Location: ../templates/login_form.php');
    exit;
}

?>

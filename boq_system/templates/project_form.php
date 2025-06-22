<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Project - BOQ System</title>
    <!-- Bootstrap 5 CSS -->
<?php
// Secure this page: redirect to login if not logged in
if (file_exists(__DIR__ . '/../src/core/config.php')) {
    require_once __DIR__ . '/../src/core/config.php'; // Ensures session_start() is called
} else {
    // Fallback if config.php is not found from this path
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to create a project.";
    // Adjust path based on your public directory structure if needed
    // Assuming 'templates' is not directly under public web root

    // Determine the correct path to login form.
    // If this template is accessed via public/index.php (router), paths might be relative to public.
    // If accessed directly (less likely in final app), paths are relative to templates dir.
    $login_page_url = 'login_form.php'; // Default if templates are in public root

    // A more robust way to define base URL might be needed in config.php
    // For now, let's assume a simple structure or use relative paths carefully.
    // If your public/index.php includes files from templates/, then relative paths from public/index.php perspective.
    // If you redirect from templates/, then paths relative to templates/.

    // Option 1: Redirect to a known public entry point that handles login redirect
    // header("Location: ../public/index.php?redirect_to_login=true");

    // Option 2: Try to redirect directly to login_form.php, assuming it's in the same dir or accessible
    // This might need adjustment based on how you route/access templates
    if (file_exists('login_form.php')) { // If login_form.php is in the same 'templates' directory
         header("Location: login_form.php");
    } elseif (file_exists('../public/index.php')) { // A common pattern is to redirect to main index
         header("Location: ../public/index.php"); // public/index.php should then redirect to login
    } else {
        // Fallback, may not work in all server setups if paths are tricky
         header("Location: /boq_system/templates/login_form.php"); // Absolute path if known
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Project - BOQ System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 700px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">Create New Project</h2>

        <!-- Placeholder for messages -->
        <div id="message-area"></div>

        <form id="createProjectForm" action="../src/modules/project/create_project.php" method="POST">
            <div class="mb-3">
                <label for="projectName" class="form-label">Project Name</label>
                <input type="text" class="form-control" id="projectName" name="project_name" required>
            </div>

            <div class="mb-3">
                <label for="projectRegion" class="form-label">Region</label>
                <input type="text" class="form-control" id="projectRegion" name="region">
            </div>

            <div class="mb-3">
                <label for="projectLocation" class="form-label">Location (e.g., Town/City, GPS Address)</label>
                <input type="text" class="form-control" id="projectLocation" name="location">
            </div>

            <div class="mb-3">
                <label for="projectCategory" class="form-label">Building Category</label>
                <select class="form-select" id="projectCategory" name="category" required>
                    <option selected disabled value="">Choose...</option>
                    <option value="Residential">Residential</option>
                    <option value="Commercial">Commercial</option>
                    <option value="Institutional">Institutional</option>
                    <option value="Industrial">Industrial</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="projectFloors" class="form-label">Number of Floors (G+)</label>
                <input type="number" class="form-control" id="projectFloors" name="floors" min="0" value="1" required>
                <small class="form-text text-muted">Enter 0 for ground floor only, 1 for G+1, etc.</small>
            </div>

            <div class="mb-3">
                <label for="projectArea" class="form-label">Total Floor Area (sqm)</label>
                <input type="number" class="form-control" id="projectArea" name="area" step="0.01" min="0">
                <small class="form-text text-muted">Approximate total floor area in square meters.</small>
            </div>

            <!-- user_id will be taken from session in the backend script -->

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Create Project</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Basic JS for form handling (optional, can be enhanced later) -->
    <script>
        // Example: Client-side validation or AJAX submission can be added here later
        // const projectForm = document.getElementById('createProjectForm');
        // projectForm.addEventListener('submit', function(event) {
        //     // Basic validation example
        //     const projectName = document.getElementById('projectName').value;
        //     if (!projectName) {
        //         event.preventDefault(); // Stop submission
        //         document.getElementById('message-area').innerHTML =
        //             '<div class="alert alert-danger">Project Name is required.</div>';
        //     }
        // });
    </script>
</body>
</html>

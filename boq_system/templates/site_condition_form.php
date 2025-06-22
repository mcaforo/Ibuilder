<?php
// Secure this page: redirect to login if not logged in
if (file_exists(__DIR__ . '/../src/core/config.php')) {
    require_once __DIR__ . '/../src/core/config.php'; // Ensures session_start() is called
} else {
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to manage site conditions.";
    header("Location: login_form.php"); // Assuming login_form is in the same templates directory
    exit;
}

// Get project_id from GET parameter - this is crucial
$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);

if (!$project_id) {
    $_SESSION['error_message'] = "No project selected or invalid project ID for site conditions.";
    // Redirect to a page where projects are listed or dashboard
    header("Location: dashboard.php"); // Or wherever projects are listed
    exit;
}

// TODO: Optional - Verify this project_id belongs to the logged-in user
// This would involve a quick DB check: SELECT user_id FROM projects WHERE id = ?
// For now, we assume if they have the project_id, they can edit (simplification)

$page_title = "Site Conditions for Project ID: " . htmlspecialchars($project_id);

// Placeholder for existing site conditions data if editing
$existing_conditions = null;
// In a full app, you would load existing conditions here if they exist for $project_id
// require_once __DIR__ . '/../src/core/Database.php';
// $db = Database::getInstance();
// $conn = $db->getConnection();
// $stmt = $conn->prepare("SELECT * FROM site_conditions WHERE project_id = :project_id");
// $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
// $stmt->execute();
// $existing_conditions = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - BOQ System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; background-color: #f8f9fa; }
        .container { max-width: 700px; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center"><?php echo $page_title; ?></h2>
        <p class="text-center"><a href="dashboard.php" class="btn btn-sm btn-outline-secondary mb-3">Back to Dashboard</a> <!-- Or project view page --></p>


        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        ?>

        <form id="siteConditionForm" action="../src/modules/site_conditions/save_site_conditions.php" method="POST">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project_id); ?>">

            <div class="mb-3">
                <label for="soilType" class="form-label">Soil Type</label>
                <select class="form-select" id="soilType" name="soil_type">
                    <option value="">Choose...</option>
                    <option value="Rocky" <?php echo ($existing_conditions && $existing_conditions['soil_type'] == 'Rocky') ? 'selected' : ''; ?>>Rocky</option>
                    <option value="Lateritic" <?php echo ($existing_conditions && $existing_conditions['soil_type'] == 'Lateritic') ? 'selected' : ''; ?>>Lateritic</option>
                    <option value="Clay" <?php echo ($existing_conditions && $existing_conditions['soil_type'] == 'Clay') ? 'selected' : ''; ?>>Clay</option>
                    <option value="Loose Fill" <?php echo ($existing_conditions && $existing_conditions['soil_type'] == 'Loose Fill') ? 'selected' : ''; ?>>Loose Fill</option>
                    <option value="Sand" <?php echo ($existing_conditions && $existing_conditions['soil_type'] == 'Sand') ? 'selected' : ''; ?>>Sand</option>
                    <option value="Other" <?php echo ($existing_conditions && $existing_conditions['soil_type'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="groundwaterTable" class="form-label">Groundwater Table</label>
                <select class="form-select" id="groundwaterTable" name="groundwater_table">
                    <option value="Unknown" <?php echo ($existing_conditions && $existing_conditions['groundwater_table'] == 'Unknown') ? 'selected' : ''; ?>>Unknown</option>
                    <option value="High" <?php echo ($existing_conditions && $existing_conditions['groundwater_table'] == 'High') ? 'selected' : ''; ?>>High</option>
                    <option value="Low" <?php echo ($existing_conditions && $existing_conditions['groundwater_table'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="terrain" class="form-label">Terrain</label>
                <select class="form-select" id="terrain" name="terrain">
                    <option value="">Choose...</option>
                    <option value="Flat" <?php echo ($existing_conditions && $existing_conditions['terrain'] == 'Flat') ? 'selected' : ''; ?>>Flat</option>
                    <option value="Sloping" <?php echo ($existing_conditions && $existing_conditions['terrain'] == 'Sloping') ? 'selected' : ''; ?>>Sloping</option>
                    <option value="Terraced" <?php echo ($existing_conditions && $existing_conditions['terrain'] == 'Terraced') ? 'selected' : ''; ?>>Terraced</option>
                    <option value="Other" <?php echo ($existing_conditions && $existing_conditions['terrain'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="recommendedFoundation" class="form-label">Recommended Foundation (Auto-suggested)</label>
                <input type="text" class="form-control" id="recommendedFoundation" name="recommended_foundation" value="<?php echo htmlspecialchars($existing_conditions['recommended_foundation'] ?? ''); ?>" readonly>
                <small class="form-text text-muted">This field will be auto-populated based on above selections. Can be manually overridden if needed (feature to be enhanced).</small>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Save Site Conditions</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Basic auto-suggestion for foundation type
        const soilTypeSelect = document.getElementById('soilType');
        const groundwaterSelect = document.getElementById('groundwaterTable');
        const terrainSelect = document.getElementById('terrain');
        const foundationInput = document.getElementById('recommendedFoundation');

        function suggestFoundation() {
            const soil = soilTypeSelect.value;
            const water = groundwaterSelect.value;
            // const terrain = terrainSelect.value; // Terrain might also influence, but keeping it simple for now

            let suggestion = "Not determined";

            if (soil === "Rocky") {
                suggestion = "Pad/Strip Footing on Rock";
            } else if (soil === "Lateritic" && water === "Low") {
                suggestion = "Strip Footing / Raft Foundation";
            } else if (soil === "Lateritic" && water === "High") {
                suggestion = "Raft Foundation / Piles (if severe)";
            } else if (soil === "Clay") {
                suggestion = (water === "High") ? "Raft Foundation / Piles / Engineered Fill" : "Strip Footing (with precautions) / Raft";
            } else if (soil === "Sand") {
                suggestion = (water === "Low") ? "Strip Footing / Raft Foundation" : "Raft Foundation / Piles";
            } else if (soil === "Loose Fill") {
                suggestion = "Engineered Fill / Piles / Raft Foundation (after soil improvement)";
            }

            // This is a very basic suggestion logic.
            // Real-world recommendations require geotechnical survey and engineering judgment.
            // Ghana Building Code GS 1207:2018 would have more specific guidelines.
            foundationInput.value = suggestion;
        }

        soilTypeSelect.addEventListener('change', suggestFoundation);
        groundwaterSelect.addEventListener('change', suggestFoundation);
        // terrainSelect.addEventListener('change', suggestFoundation); // Add if terrain logic is included

        // Initial suggestion based on loaded values (if any)
        // This part is tricky if $existing_conditions are not pre-loaded via PHP for the form fields.
        // If the form fields are populated by PHP like in the <option selected> example,
        // then call suggestFoundation() on page load.
        document.addEventListener('DOMContentLoaded', suggestFoundation);
    </script>
</body>
</html>

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Initial Project Setup:**
    - Basic directory structure for `public`, `src`, `templates`, `data`.
    - Placeholder `public/index.php`.
    - Initial `README.md` and `changelog.md`.
- **Database Schema:**
    - `data/sql/schema.sql` created with tables: `users`, `projects`, `site_conditions`, `materials`, `boq_items`, `settings`.
    - Includes basic settings and sample material/labor rates.
- **Core Functionality:**
    - `src/core/config.php`: Configuration for database credentials, app settings, session start.
    - `src/core/Database.php`: Singleton PDO database connection class.
- **User Management Module:**
    - `templates/register_form.php`: User registration HTML form with Bootstrap styling.
    - `src/auth/register.php`: Backend for user registration, including validation, password hashing (`password_hash`), and database insertion.
    - `templates/login_form.php`: User login HTML form.
    - `src/auth/login.php`: Backend for user login, including password verification (`password_verify`), session creation, and redirection.
    - `src/auth/logout.php`: User logout script, destroys session.
- **Project Entry Module:**
    - `templates/project_form.php`: Form for creating new projects. Secured to require login.
    - `src/modules/project/create_project.php`: Backend for project creation. Uses logged-in user's session ID, validates inputs, and saves project data to the database.
- **Site Conditions Module:**
    - `templates/site_condition_form.php`: Form for adding/editing site conditions for a specific project. Requires login and `project_id`. Includes basic client-side JavaScript for auto-suggesting foundation type.
    - `src/modules/site_conditions/save_site_conditions.php`: Backend for saving site conditions. Validates input, checks project ownership (basic), and uses an UPSERT operation (INSERT ON DUPLICATE KEY UPDATE) to save data.
- **Routing & Dashboard:**
    - `public/index.php`: Updated to act as a basic router.
        - Directs unauthenticated users to login/registration.
        - Directs authenticated users to a dashboard or other actions.
        - Dynamically creates a basic `templates/dashboard.php` if it doesn't exist, serving as a landing page for logged-in users.
- **General:**
    - Consistent use of Bootstrap 5 for basic styling in templates.
    - Session-based flash messages for user feedback (success/error).
    - Basic error logging for critical operations.
    - Use of PDO prepared statements for database interactions to prevent SQL injection.

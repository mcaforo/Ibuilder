-- Database Schema for BOQ Automation System
-- All IDs are BIGINT UNSIGNED for future scalability, unless specified otherwise.

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0; -- Disable checks for initial setup
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- Hashed password
  `role` ENUM('Admin', 'Registered User') NOT NULL DEFAULT 'Registered User',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `projects`
--
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `region` VARCHAR(100) DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `category` ENUM('Residential', 'Commercial', 'Institutional', 'Industrial') DEFAULT NULL,
  `floors` INT UNSIGNED DEFAULT 1,
  `area` DECIMAL(10,2) DEFAULT NULL, -- Total floor area in square meters
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, -- If user is deleted, their projects are deleted
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `site_conditions`
--
DROP TABLE IF EXISTS `site_conditions`;
CREATE TABLE `site_conditions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` BIGINT UNSIGNED NOT NULL,
  `soil_type` ENUM('Rocky', 'Lateritic', 'Clay', 'Loose Fill', 'Sand', 'Other') DEFAULT NULL,
  `groundwater_table` ENUM('High', 'Low', 'Unknown') DEFAULT 'Unknown',
  `terrain` ENUM('Flat', 'Sloping', 'Terraced', 'Other') DEFAULT NULL,
  `recommended_foundation` VARCHAR(255) DEFAULT NULL, -- Can be auto-determined
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id_unique` (`project_id`), -- Each project has one site condition entry
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `materials` (for default rates)
-- This table can also be used for labor rates by specifying type
--
DROP TABLE IF EXISTS `materials`;
CREATE TABLE `materials` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE, -- e.g., Cement, Sand, Gravel, Labor - Mason
  `unit` VARCHAR(50) NOT NULL, -- e.g., bag, m3, ton, day, hr
  `rate` DECIMAL(10,2) NOT NULL,
  `type` ENUM('Material', 'Labor', 'Equipment') NOT NULL DEFAULT 'Material',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_name_type` (`name`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `boq_items`
--
DROP TABLE IF EXISTS `boq_items`;
CREATE TABLE `boq_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` BIGINT UNSIGNED NOT NULL,
  `item_code` VARCHAR(50) DEFAULT NULL, -- Optional item code (e.g., A.1.1)
  `description` TEXT NOT NULL,
  `unit` VARCHAR(50) NOT NULL, -- m, m2, m3, pcs, kg, item, sum
  `quantity` DECIMAL(12,3) NOT NULL,
  `rate` DECIMAL(10,2) NOT NULL, -- Unit rate
  `total` DECIMAL(12,2) NOT NULL, -- quantity * rate
  `category` VARCHAR(100) DEFAULT NULL, -- e.g., Substructure, Superstructure, Finishes
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  INDEX `idx_project_id` (`project_id`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `settings` (for global settings like tax, contingency)
--
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE, -- e.g., 'vat_rate', 'contingency_rate', 'currency_symbol'
  `setting_value` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Insert default settings (can be managed by Admin later)
--
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('vat_rate', '0.05', 'VAT rate (e.g., 0.05 for 5%)'),
('contingency_rate', '0.10', 'Contingency rate (e.g., 0.10 for 10%)'),
('currency_symbol', 'GHS', 'Default currency symbol (e.g., GHS, USD)');

SET foreign_key_checks = 1; -- Re-enable checks

--
-- Dummy Data (Optional - for initial testing)
-- Note: User creation requires hashed passwords. This will be handled by the application.
--
-- Example: Add a default admin user (password needs to be hashed by PHP script)
-- INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
-- ('Admin User', 'admin@boqsystem.com', 'hashed_password_here', 'Admin');

-- Example: Default material/labor rates
INSERT INTO `materials` (`name`, `unit`, `rate`, `type`) VALUES
('Ordinary Portland Cement (50kg)', 'bag', 50.00, 'Material'),
('Sand (Fine aggregate)', 'm3', 120.00, 'Material'),
('Gravel (Coarse aggregate)', 'm3', 150.00, 'Material'),
('6" Concrete Blocks', 'pcs', 4.50, 'Material'),
('High Tensile Steel (12mm)', 'ton', 5500.00, 'Material'),
('Timber (Wawa - Sawn)', 'm3', 1200.00, 'Material'),
('Skilled Labor (Mason/Carpenter)', 'day', 100.00, 'Labor'),
('Unskilled Labor', 'day', 60.00, 'Labor');

-- Further dummy data for projects, boq_items etc. can be added once the application logic is in place.

-- End of Schema
-- Remember to create the database itself, e.g., CREATE DATABASE boq_system_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- And then run this script against that database.

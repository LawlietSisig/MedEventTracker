<?php
/**
 * Patients Table Setup
 * Run once at: /config/setup_patients.php
 */

require_once __DIR__ . '/database.php';

$conn = getConnection();

$sql = "CREATE TABLE IF NOT EXISTS `patients` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `first_name`     VARCHAR(100) NOT NULL,
    `last_name`      VARCHAR(100) NOT NULL,
    `dob`            DATE NOT NULL,
    `gender`         ENUM('Male','Female','Other') NOT NULL,
    `contact_number` VARCHAR(50),
    `address`        TEXT,
    `blood_type`     VARCHAR(5),
    `medical_notes`  TEXT,
    `created_by`     INT,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ patients table created (or already exists).\n";
} else {
    echo "❌ Error: " . $conn->error . "\n";
}

$conn->close();

<?php
/**
 * Volunteers Table Setup
 * Medical Outreach Tracker
 * Run once at: /Medical Outreach Tracker/config/setup_volunteers.php
 */

require_once __DIR__ . '/database.php';

$conn = getConnection();

$sql = "CREATE TABLE IF NOT EXISTS `volunteers` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `first_name`     VARCHAR(100) NOT NULL,
    `last_name`      VARCHAR(100) NOT NULL,
    `email`          VARCHAR(255) NOT NULL,
    `contact_number` VARCHAR(50),
    `profession`     VARCHAR(100) NOT NULL,
    `status`         ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `skills_notes`   TEXT,
    `created_by`     INT NOT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ volunteers table created (or already exists).<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

$conn->close();
echo "<br><a href='../pages/volunteers.php'>Go to Volunteers</a>";

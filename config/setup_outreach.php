<?php
/**
 * Outreach Events Table Setup
 * Medical Outreach Tracker
 * Run once at: /Medical Outreach Tracker/config/setup_outreach.php
 */

require_once __DIR__ . '/database.php';

$conn = getConnection();

$sql = "CREATE TABLE IF NOT EXISTS `outreach_events` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `title`         VARCHAR(255) NOT NULL,
    `description`   TEXT,
    `location`      VARCHAR(255) NOT NULL,
    `event_date`    DATE NOT NULL,
    `start_time`    TIME NOT NULL,
    `end_time`      TIME NOT NULL,
    `status`        ENUM('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
    `max_volunteers` INT UNSIGNED DEFAULT NULL,
    `created_by`    INT NOT NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ outreach_events table created (or already exists).<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

$conn->close();
echo "<br><a href='../pages/outreach_events.php'>Go to Outreach Events</a>";

<?php
/**
 * Outreach Events Table Setup
 * Medical Outreach Tracker
 * Run once at: /Medical Outreach Tracker/config/setup_outreach.php
 */

require_once __DIR__ . '/database.php';

$conn = getConnection();

$sql = "CREATE TABLE IF NOT EXISTS `outreach_events` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `title`          VARCHAR(255) NOT NULL,
    `description`    TEXT,
    `location`       VARCHAR(255) NOT NULL,
    `event_date`     DATE NOT NULL,
    `end_event_date` DATE DEFAULT NULL,
    `start_time`     TIME NOT NULL,
    `end_time`       TIME NOT NULL,
    `status`         ENUM('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
    `max_volunteers` INT UNSIGNED DEFAULT NULL,
    `created_by`     INT NOT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "&#x2705; outreach_events table created (or already exists).<br>";
} else {
    echo "&#x274C; Error: " . $conn->error . "<br>";
}

// ── Migration: add end_event_date column if it doesn't exist yet ───────────────
$checkCol = $conn->query("SHOW COLUMNS FROM `outreach_events` LIKE 'end_event_date'");
if ($checkCol && $checkCol->num_rows === 0) {
    $alter = "ALTER TABLE `outreach_events` ADD COLUMN `end_event_date` DATE DEFAULT NULL AFTER `event_date`";
    if ($conn->query($alter) === TRUE) {
        echo "&#x2705; Column <code>end_event_date</code> added to outreach_events.<br>";
    } else {
        echo "&#x274C; Migration error: " . $conn->error . "<br>";
    }
} else {
    echo "&#x2139;&#xFE0F; Column <code>end_event_date</code> already exists — skipped.<br>";
}

$conn->close();
echo "<br><a href='../pages/outreach_events.php'>Go to Outreach Events</a>";

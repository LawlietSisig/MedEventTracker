<?php
require_once __DIR__ . '/config/database.php';
$conn = getConnection();

$sql = "CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(64) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql)) {
    echo "✅ password_resets table created (or already exists).";
} else {
    echo "❌ Error: " . $conn->error;
}
$conn->close();

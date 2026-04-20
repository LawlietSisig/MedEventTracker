<?php
/**
 * Database Setup Script
 * Run this once to create the database and tables
 */

require_once __DIR__ . '/database.php';

$conn = getConnectionNoDB();

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

$conn->select_db(DB_NAME);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'coordinator', 'volunteer') NOT NULL DEFAULT 'volunteer',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully.<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create login_attempts table for brute force protection
$sql = "CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Login attempts table created successfully.<br>";
} else {
    echo "Error creating login_attempts table: " . $conn->error . "<br>";
}

// Create remember_tokens table
$sql = "CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Remember tokens table created successfully.<br>";
} else {
    echo "Error creating remember_tokens table: " . $conn->error . "<br>";
}

// Insert default admin user
$adminEmail = 'admin@medoutreach.com';
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $adminEmail);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $firstName = 'System';
    $lastName = 'Administrator';
    $role = 'admin';
    $stmt->bind_param("sssss", $firstName, $lastName, $adminEmail, $adminPassword, $role);
    
    if ($stmt->execute()) {
        echo "Default admin user created.<br>";
        echo "Email: admin@medoutreach.com<br>";
        echo "Password: admin123<br>";
        echo "<strong>Please change this password after first login!</strong><br>";
    } else {
        echo "Error creating admin user: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "Admin user already exists.<br>";
}

$check->close();

// Create email_verifications table for OTP flow
$sql = "CREATE TABLE IF NOT EXISTS `email_verifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `otp_hash` VARCHAR(255) NOT NULL,
    `form_data` TEXT NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `attempts` TINYINT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Email verifications table created successfully.<br>";
} else {
    echo "Error creating email verifications table: " . $conn->error . "<br>";
}

$conn->close();

echo "<br><a href='../index.php'>Go to Login</a>";

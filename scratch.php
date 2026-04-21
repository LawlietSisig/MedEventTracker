<?php
require_once __DIR__ . '/config/database.php';

$conn = getConnection();

// Check if middle_name column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'middle_name'");
if ($result->num_rows == 0) {
    // Add middle_name column
    $conn->query("ALTER TABLE users ADD COLUMN middle_name VARCHAR(100) NULL AFTER first_name");
    echo "Added middle_name column to users table.\n";
} else {
    echo "middle_name column already exists in users table.\n";
}

// Describe the table
$res = $conn->query("DESCRIBE users");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

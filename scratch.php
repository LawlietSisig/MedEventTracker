<?php
require_once __DIR__ . '/config/database.php';
$conn = getConnection();
$result = $conn->query("SHOW TABLES LIKE 'volunteers'");
if ($result->num_rows > 0) {
    echo "volunteers table exists:\n";
    $cols = $conn->query("SHOW COLUMNS FROM volunteers");
    while ($row = $cols->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "volunteers table does NOT exist.\n";
}
$conn->close();

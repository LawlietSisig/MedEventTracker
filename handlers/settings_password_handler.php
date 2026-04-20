<?php
/**
 * Handle Settings Password Update
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/settings.php');
    exit();
}

$user = getCurrentUser();
$userId = $user['id'];

// Get POST data
$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// ── Validation ───────────────────────────────────────────────────────────────
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    setFlash('error', 'Please fill in all password fields.');
    header('Location: ../pages/settings.php');
    exit();
}

if ($newPassword !== $confirmPassword) {
    setFlash('error', 'New passwords do not match.');
    header('Location: ../pages/settings.php');
    exit();
}

if (strlen($newPassword) < 8) {
    setFlash('error', 'New password must be at least 8 characters long.');
    header('Location: ../pages/settings.php');
    exit();
}

// ── Verify Current Password ──────────────────────────────────────────────────
$conn = getConnection();
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    setFlash('error', 'User account not found.');
    $stmt->close();
    $conn->close();
    header('Location: ../handlers/logout_handler.php');
    exit();
}

$userData = $result->fetch_assoc();
$stmt->close();

if (!password_verify($currentPassword, $userData['password'])) {
    setFlash('error', 'Incorrect current password.');
    $conn->close();
    header('Location: ../pages/settings.php');
    exit();
}

// ── Update Password ──────────────────────────────────────────────────────────
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hashedPassword, $userId);

if ($stmt->execute()) {
    setFlash('success', 'Password updated successfully!');
} else {
    setFlash('error', 'An error occurred while updating your password.');
}

$stmt->close();
$conn->close();

header('Location: ../pages/settings.php');
exit();

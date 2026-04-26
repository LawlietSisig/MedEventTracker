<?php
/**
 * Manage Users Handler
 * Handles: edit_role, toggle_status, delete_user
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
$currentUser = getCurrentUser();

// Only admins may manage users
if ($currentUser['role'] !== 'admin') {
    header('Location: ../pages/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/manage_users.php');
    exit();
}

$action = $_POST['action'] ?? '';
$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    setFlash('error', 'Invalid user.');
    header('Location: ../pages/manage_users.php');
    exit();
}

// Prevent admins from modifying themselves via this panel
if ($userId === (int)$currentUser['id']) {
    setFlash('error', 'You cannot modify your own account from this panel.');
    header('Location: ../pages/manage_users.php');
    exit();
}

$conn = getConnection();

switch ($action) {

    // ── Edit Role ─────────────────────────────────────────────
    case 'edit_role':
        $role = $_POST['role'] ?? '';
        $allowed = ['volunteer', 'coordinator', 'admin'];
        if (!in_array($role, $allowed, true)) {
            setFlash('error', 'Invalid role selected.');
            break;
        }
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $userId);
        if ($stmt->execute()) {
            setFlash('success', 'User role updated successfully.');
        } else {
            setFlash('error', 'Failed to update role. Please try again.');
        }
        $stmt->close();
        break;

    // ── Toggle Active Status ───────────────────────────────────
    case 'toggle_status':
        // Flip the current is_active value
        $stmt = $conn->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            setFlash('success', 'User status updated.');
        } else {
            setFlash('error', 'Failed to update status. Please try again.');
        }
        $stmt->close();
        break;

    // ── Reset Password ─────────────────────────────────────────
    case 'reset_password':
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (strlen($newPassword) < 8) {
            setFlash('error', 'Password must be at least 8 characters.');
            break;
        }
        if ($newPassword !== $confirmPassword) {
            setFlash('error', 'Passwords do not match.');
            break;
        }
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $userId);
        if ($stmt->execute()) {
            setFlash('success', 'Password reset successfully.');
        } else {
            setFlash('error', 'Failed to reset password. Please try again.');
        }
        $stmt->close();
        break;

    // ── Delete User ────────────────────────────────────────────
    case 'delete_user':
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            setFlash('success', 'User account deleted.');
        } else {
            setFlash('error', 'Failed to delete user. Please try again.');
        }
        $stmt->close();
        break;

    default:
        setFlash('error', 'Unknown action.');
        break;
}

$conn->close();
header('Location: ../pages/manage_users.php');
exit();

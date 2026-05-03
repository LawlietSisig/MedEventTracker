<?php
/**
 * Reset Password Handler
 * Medical Outreach Tracker
 * Validates token and updates the user's password.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Medical Outreach Tracker/index.php');
    exit();
}

$token   = trim($_POST['token'] ?? '');
$pw      = $_POST['password']         ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// ── Basic validation ──────────────────────────────────────────────────────────
if (!$token) {
    setFlash('error', 'Invalid reset request.');
    header('Location: /Medical Outreach Tracker/index.php');
    exit();
}

if (strlen($pw) < 8) {
    setFlash('error', 'Password must be at least 8 characters.');
    header('Location: /Medical Outreach Tracker/reset_password.php?token=' . urlencode($token));
    exit();
}

if ($pw !== $confirm) {
    setFlash('error', 'Passwords do not match.');
    header('Location: /Medical Outreach Tracker/reset_password.php?token=' . urlencode($token));
    exit();
}

// ── Look up token ─────────────────────────────────────────────────────────────
$conn = getConnection();

$stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || strtotime($row['expires_at']) <= time()) {
    $conn->close();
    setFlash('error', 'This reset link is invalid or has expired. Please request a new one.');
    header('Location: /Medical Outreach Tracker/forgot_password.php');
    exit();
}

$email = $row['email'];

// ── Update the password ───────────────────────────────────────────────────────
$hashed = password_hash($pw, PASSWORD_DEFAULT);

$upd = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$upd->bind_param('ss', $hashed, $email);

if (!$upd->execute() || $upd->affected_rows === 0) {
    $upd->close();
    $conn->close();
    setFlash('error', 'Could not update your password. Please try again.');
    header('Location: /Medical Outreach Tracker/reset_password.php?token=' . urlencode($token));
    exit();
}
$upd->close();

// ── Invalidate the token ──────────────────────────────────────────────────────
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param('s', $email);
$del->execute();
$del->close();
$conn->close();

setFlash('success', 'Password updated successfully! Please sign in with your new password.');
header('Location: /Medical Outreach Tracker/index.php');
exit();

<?php
/**
 * Forgot Password Handler
 * Medical Outreach Tracker
 * Generates a secure reset token, stores it, and emails the link.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Medical Outreach Tracker/forgot_password.php');
    exit();
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
    header('Location: /Medical Outreach Tracker/forgot_password.php');
    exit();
}

$conn = getConnection();

// Always show the same success message regardless of whether the email exists
// (prevents email enumeration)
$genericMsg = 'If that email is registered, you will receive a reset link shortly. Check your inbox (and spam folder).';

// Look up the user
$stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    // Don't reveal whether the email exists
    $conn->close();
    setFlash('success', $genericMsg);
    header('Location: /Medical Outreach Tracker/forgot_password.php');
    exit();
}

// ── Generate a secure token ───────────────────────────────────────────────────
$token     = bin2hex(random_bytes(32)); // 64-char hex string
$expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

// Delete any existing reset tokens for this email
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param('s', $email);
$del->execute();
$del->close();

// Insert new token
$ins = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$ins->bind_param('sss', $email, $token, $expiresAt);
$ins->execute();
$ins->close();
$conn->close();

// ── Send reset email ──────────────────────────────────────────────────────────
$resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/Medical Outreach Tracker/reset_password.php?token=' . $token;
$result    = sendPasswordResetEmail($email, $user['first_name'], $resetLink);

// Either way, show generic success (don't leak info)
setFlash('success', $genericMsg);
header('Location: /Medical Outreach Tracker/forgot_password.php');
exit();

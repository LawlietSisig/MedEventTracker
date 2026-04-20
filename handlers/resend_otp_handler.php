<?php
/**
 * Resend OTP Handler
 * Rate-limited: one resend per 60 seconds
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Medical Outreach Tracker/pages/verify_otp.php');
    exit();
}

if (empty($_SESSION['pending_email'])) {
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

$pendingEmail = $_SESSION['pending_email'];
$pendingName  = $_SESSION['pending_name'] ?? 'User';

$conn = getConnection();

// Fetch current record
$stmt = $conn->prepare(
    "SELECT id, created_at, expires_at FROM email_verifications
     WHERE email = ? ORDER BY created_at DESC LIMIT 1"
);
$stmt->bind_param("s", $pendingEmail);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    $conn->close();
    setFlash('error', 'No pending verification found. Please register again.');
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

// ── Rate limit: 60 seconds between resends ───────────────────
$secondsSinceCreated = time() - strtotime($row['created_at']);
if ($secondsSinceCreated < 60) {
    $wait = 60 - $secondsSinceCreated;
    $conn->close();
    setFlash('error', "Please wait {$wait} seconds before requesting a new code.");
    header('Location: /Medical Outreach Tracker/pages/verify_otp.php');
    exit();
}

// ── Generate new OTP ──────────────────────────────────────────
$otpCode  = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otpHash  = password_hash($otpCode, PASSWORD_DEFAULT);
$expiresAt = date('Y-m-d H:i:s', time() + 600);

$upd = $conn->prepare(
    "UPDATE email_verifications
     SET otp_hash = ?, expires_at = ?, attempts = 0, created_at = NOW()
     WHERE id = ?"
);
$upd->bind_param("ssi", $otpHash, $expiresAt, $row['id']);
$upd->execute();
$upd->close();
$conn->close();

// ── Send new email ────────────────────────────────────────────
$result = sendOtpEmail($pendingEmail, $pendingName, $otpCode);

if (!$result['success']) {
    setFlash('error', 'Could not send email. Please try again later.');
} else {
    setFlash('success', 'A new verification code has been sent to your email.');
}

header('Location: /Medical Outreach Tracker/pages/verify_otp.php');
exit();

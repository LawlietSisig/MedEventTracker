<?php
/**
 * Verify OTP Handler
 * Checks the submitted OTP and creates the user account on success
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Medical Outreach Tracker/pages/verify_otp.php');
    exit();
}

// ── Require a pending email in session ───────────────────────
if (empty($_SESSION['pending_email'])) {
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

$pendingEmail = $_SESSION['pending_email'];

// ── Collect OTP from the single box ────────────────────────────
$otpSubmitted = preg_replace('/\D/', '', $_POST['otp_code'] ?? '');

if (strlen($otpSubmitted) !== 6) {
    setFlash('error', 'Please enter all 6 digits of the verification code.');
    header('Location: /Medical Outreach Tracker/pages/verify_otp.php');
    exit();
}

// ── Look up pending verification ─────────────────────────────
$conn = getConnection();

$stmt = $conn->prepare(
    "SELECT id, otp_hash, form_data, expires_at, attempts
     FROM email_verifications
     WHERE email = ?
     ORDER BY created_at DESC
     LIMIT 1"
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

// ── Check expiry ─────────────────────────────────────────────
if (strtotime($row['expires_at']) < time()) {
    // Delete expired record
    $del = $conn->prepare("DELETE FROM email_verifications WHERE id = ?");
    $del->bind_param("i", $row['id']);
    $del->execute();
    $del->close();
    $conn->close();

    unset($_SESSION['pending_email'], $_SESSION['pending_name']);
    setFlash('error', 'Your verification code has expired. Please register again.');
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

// ── Check attempt limit ──────────────────────────────────────
if ($row['attempts'] >= 5) {
    $conn->close();
    setFlash('error', 'Too many incorrect attempts. Please register again.');
    unset($_SESSION['pending_email'], $_SESSION['pending_name']);
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

// ── Verify OTP ───────────────────────────────────────────────
if (!password_verify($otpSubmitted, $row['otp_hash'])) {
    // Increment attempts
    $upd = $conn->prepare("UPDATE email_verifications SET attempts = attempts + 1 WHERE id = ?");
    $upd->bind_param("i", $row['id']);
    $upd->execute();
    $upd->close();

    $remaining = 4 - (int)$row['attempts'];
    $conn->close();

    $msg = $remaining > 0
        ? "Incorrect code. You have {$remaining} attempt(s) remaining."
        : 'Too many incorrect attempts. Please register again.';

    if ($remaining <= 0) {
        unset($_SESSION['pending_email'], $_SESSION['pending_name']);
        header('Location: /Medical Outreach Tracker/register.php');
    } else {
        header('Location: /Medical Outreach Tracker/pages/verify_otp.php');
    }
    setFlash('error', $msg);
    exit();
}

// ── OTP is valid — create the user account ───────────────────
$formData = json_decode($row['form_data'], true);

if (!$formData) {
    $conn->close();
    setFlash('error', 'Registration data is corrupted. Please register again.');
    unset($_SESSION['pending_email'], $_SESSION['pending_name']);
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

// Check one more time that email isn't already taken (race condition guard)
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $formData['email']);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    $conn->close();
    unset($_SESSION['pending_email'], $_SESSION['pending_name']);
    setFlash('error', 'An account with that email already exists. Please sign in.');
    header('Location: /Medical Outreach Tracker/index.php');
    exit();
}
$check->close();

// Insert the user
$ins = $conn->prepare(
    "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)"
);
$ins->bind_param(
    "sssss",
    $formData['first_name'],
    $formData['last_name'],
    $formData['email'],
    $formData['hashed_password'],
    $formData['role']
);

if (!$ins->execute()) {
    $ins->close();
    $conn->close();
    setFlash('error', 'Account creation failed. Please try again.');
    header('Location: /Medical Outreach Tracker/pages/verify_otp.php');
    exit();
}
$ins->close();

// Clean up verification record
$del = $conn->prepare("DELETE FROM email_verifications WHERE email = ?");
$del->bind_param("s", $pendingEmail);
$del->execute();
$del->close();
$conn->close();

// Clear session
unset($_SESSION['pending_email'], $_SESSION['pending_name']);

setFlash('success', 'Account verified and created! Welcome aboard — please sign in.');
header('Location: /Medical Outreach Tracker/index.php');
exit();

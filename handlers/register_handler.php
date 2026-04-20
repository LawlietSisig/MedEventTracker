<?php
/**
 * Register Form Handler
 * Validates input, generates OTP, sends email, stores pending verification
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

// ── Collect & sanitise inputs ────────────────────────────────
$firstName = trim(strip_tags($_POST['first_name'] ?? ''));
$lastName  = trim(strip_tags($_POST['last_name']  ?? ''));
$email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$role      = $_POST['role']             ?? '';
$password  = $_POST['password']         ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

// ── Validation ───────────────────────────────────────────────
$errors = [];

if (empty($firstName) || strlen($firstName) > 100) {
    $errors[] = 'First name is required (max 100 characters).';
}
if (empty($lastName) || strlen($lastName) > 100) {
    $errors[] = 'Last name is required (max 100 characters).';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (!in_array($role, ['volunteer', 'coordinator'], true)) {
    $errors[] = 'Please select a valid role.';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

// ── Check email isn't already registered ────────────────────
$conn = getConnection();

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    setFlash('error', 'An account with that email already exists. Please sign in.');
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}
$stmt->close();

// ── Generate OTP ─────────────────────────────────────────────
$otpCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otpHash = password_hash($otpCode, PASSWORD_DEFAULT);

// ── Store pending verification ───────────────────────────────
// Remove any previous pending verifications for this email
$stmt = $conn->prepare("DELETE FROM email_verifications WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

// Store form data as JSON (password already hashed so we don't store plaintext)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$formData = json_encode([
    'first_name'      => $firstName,
    'last_name'       => $lastName,
    'email'           => $email,
    'hashed_password' => $hashedPassword,
    'role'            => $role,
]);

$expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes

$stmt = $conn->prepare(
    "INSERT INTO email_verifications (email, otp_hash, form_data, expires_at) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $email, $otpHash, $formData, $expiresAt);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    setFlash('error', 'Something went wrong. Please try again.');
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}
$stmt->close();
$conn->close();

// ── Send OTP email ───────────────────────────────────────────
$result = sendOtpEmail($email, $firstName, $otpCode);

if (!$result['success']) {
    setFlash('error', 'Could not send verification email. Please check your mail config or try again.');
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

// ── Store pending email in session & redirect ────────────────
$_SESSION['pending_email'] = $email;
$_SESSION['pending_name']  = $firstName;

header('Location: /Medical Outreach Tracker/pages/verify_otp.php');
exit();

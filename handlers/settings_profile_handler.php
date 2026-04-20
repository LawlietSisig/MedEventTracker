<?php
/**
 * Handle Settings Profile Update
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
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim(strtolower($_POST['email'] ?? ''));

// ── Validation ───────────────────────────────────────────────────────────────
if (empty($firstName) || empty($lastName) || empty($email)) {
    setFlash('error', 'Please fill in all required fields.');
    header('Location: ../pages/settings.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
    header('Location: ../pages/settings.php');
    exit();
}

// ── Check if email is already taken by another user ──────────────────────────
$conn = getConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    setFlash('error', 'That email address is already in use by another account.');
    $stmt->close();
    $conn->close();
    header('Location: ../pages/settings.php');
    exit();
}
$stmt->close();

// ── Handle Avatar Upload ─────────────────────────────────────────────────────
$avatarPath = $user['avatar']; // Keep current avatar by default

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        setFlash('error', 'An error occurred during file upload.');
        header('Location: ../pages/settings.php');
        exit();
    }

    $file = $_FILES['avatar'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if ($file['size'] > $maxSize) {
        setFlash('error', 'Image file is too large. Maximum size is 2MB.');
        header('Location: ../pages/settings.php');
        exit();
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    
    // Sometimes mime_content_type fails or isn't perfect, let's also check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($fileType, $allowedTypes) || !in_array($ext, $allowedExts)) {
        setFlash('error', 'Invalid file type. Please upload a JPG, PNG, or WebP image.');
        header('Location: ../pages/settings.php');
        exit();
    }

    // Prepare upload directory
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
    $targetFilePath = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // Delete old avatar if it exists
        if (!empty($avatarPath) && file_exists(__DIR__ . '/../' . $avatarPath)) {
            unlink(__DIR__ . '/../' . $avatarPath);
        }
        $avatarPath = 'uploads/avatars/' . $newFileName;
    } else {
        setFlash('error', 'Failed to save uploaded file.');
        header('Location: ../pages/settings.php');
        exit();
    }
}

// ── Update Database ──────────────────────────────────────────────────────────
$stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, avatar = ? WHERE id = ?");
$stmt->bind_param("ssssi", $firstName, $lastName, $email, $avatarPath, $userId);

if ($stmt->execute()) {
    // Update session data so UI updates immediately
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;
    $_SESSION['email']      = $email;
    $_SESSION['avatar']     = $avatarPath;

    setFlash('success', 'Profile updated successfully.');
} else {
    setFlash('error', 'An error occurred while saving your profile.');
}

$stmt->close();
$conn->close();

header('Location: ../pages/settings.php');
exit();

<?php
/**
 * Login Form Handler
 * Processes login form submission
 */

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Medical Outreach Tracker/index.php');
    exit();
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

// Validate inputs
if (empty($email) || empty($password)) {
    setFlash('error', 'Please fill in all fields.');
    header('Location: /Medical Outreach Tracker/index.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
    header('Location: /Medical Outreach Tracker/index.php');
    exit();
}

// Attempt login
$result = attemptLogin($email, $password);

if ($result['success']) {
    header('Location: /Medical Outreach Tracker/pages/dashboard.php');
} else {
    setFlash('error', $result['message']);
    header('Location: /Medical Outreach Tracker/index.php');
}
exit();

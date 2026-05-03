<?php
/**
 * Session Management
 * Medical Outreach Tracker
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require authentication — redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /Medical Outreach Tracker/index.php');
        exit();
    }
}

/**
 * Redirect if already logged in
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: /Medical Outreach Tracker/pages/dashboard.php');
        exit();
    }
}

/**
 * Get current user data from session
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    return [
        'id'          => $_SESSION['user_id'],
        'first_name'  => $_SESSION['first_name'],
        'middle_name' => $_SESSION['middle_name'] ?? null,
        'last_name'   => $_SESSION['last_name'],
        'email'       => $_SESSION['email'],
        'role'        => $_SESSION['role'],
        'avatar'      => $_SESSION['avatar'] ?? null,
    ];
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

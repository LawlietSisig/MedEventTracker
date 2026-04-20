<?php
/**
 * Authentication Logic
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';

/**
 * Attempt to log in a user
 * Returns: ['success' => bool, 'message' => string]
 */
function attemptLogin($email, $password) {
    $conn = getConnection();
    
    // Check login attempts (brute force protection)
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['attempts'] >= 5) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Too many login attempts. Please try again in 15 minutes.'];
    }
    $stmt->close();
    
    // Look up user
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, is_active, avatar FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$user || !password_verify($password, $user['password'])) {
        // Record failed attempt
        $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $ip);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    
    if (!$user['is_active']) {
        $conn->close();
        return ['success' => false, 'message' => 'Your account has been deactivated. Please contact an administrator.'];
    }
    
    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $stmt->close();
    
    // Clear login attempts for this IP
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->close();
    
    // Set session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name']  = $user['last_name'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['avatar']     = $user['avatar'] ?? null;
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    $conn->close();
    return ['success' => true, 'message' => 'Login successful.'];
}

/**
 * Log out the current user
 */
function logout() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

<?php
/**
 * Logout Handler
 */

require_once __DIR__ . '/../includes/auth.php';

logout();

setFlash('success', 'You have been logged out successfully.');
header('Location: /Medical Outreach Tracker/index.php');
exit();

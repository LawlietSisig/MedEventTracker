<?php
/**
 * Forgot Password Page
 * Medical Outreach Tracker
 */

require_once __DIR__ . '/includes/session.php';
redirectIfLoggedIn();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Forgot Password — Medical Outreach Tracker">
    <title>Forgot Password — Medical Outreach Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏥</text></svg>">
</head>
<body>
    <div class="login-page">
        <!-- Left Branding Panel -->
        <div class="login-branding">
            <div class="floating-elements">
                <span class="floating-icon">🏥</span>
                <span class="floating-icon">💊</span>
                <span class="floating-icon">🩺</span>
                <span class="floating-icon">❤️</span>
                <span class="floating-icon">🌍</span>
                <span class="floating-icon">📋</span>
            </div>
            <div class="branding-content">
                <div class="brand-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <h1>Medical <span>Outreach</span> Tracker</h1>
                <p>We'll send a password reset link to your registered email address.</p>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="login-form-panel">
            <div class="login-form-container">
                <div class="login-form-header">
                    <div class="mobile-logo">
                        <div class="mobile-logo-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <span class="mobile-logo-text">Medical Outreach Tracker</span>
                    </div>
                    <h2>Reset your password</h2>
                    <p>Enter your account email and we'll send you a reset link</p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" id="flash-alert">
                        <?php if ($flash['type'] === 'error'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        <?php elseif ($flash['type'] === 'success'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <form action="handlers/forgot_password_handler.php" method="POST" id="forgot-form">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </span>
                            <input type="email" class="form-input" id="email" name="email"
                                   placeholder="Enter your registered email" required autocomplete="email">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="submit-btn">
                        <span>
                            <span class="btn-text">Send Reset Link</span>
                        </span>
                    </button>
                </form>

                <p class="auth-switch" style="margin-top: var(--space-6);">
                    Remember your password?
                    <a href="/Medical Outreach Tracker/index.php" class="auth-switch-link">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

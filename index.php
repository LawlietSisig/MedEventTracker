<?php
/**
 * Medical Outreach Tracker — Login Page
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
    <meta name="description" content="Medical Outreach Tracker — Manage and track community health outreach programs efficiently.">
    <title>Login — Medical Outreach Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <p>Streamline your community health outreach programs. Track patients, manage events, and measure impact — all in one place.</p>
                
                <div class="brand-stats">
                    <div class="stat-item">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Communities</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">1.2K</span>
                        <span class="stat-label">Patients</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Satisfaction</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Login Form Panel -->
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
                    <h2>Welcome back</h2>
                    <p>Enter your credentials to access your account</p>
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

                <form action="handlers/login_handler.php" method="POST" id="login-form">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </span>
                            <input type="email" class="form-input" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </span>
                            <input type="password" class="form-input" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" id="password-toggle" aria-label="Toggle password visibility">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" id="eye-icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-extras">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkbox-label">Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-primary" id="login-btn">
                        <span>
                            <span class="btn-text">Sign In</span>
                            <span class="btn-spinner"></span>
                        </span>
                    </button>
                </form>

                <p class="auth-switch">
                    Don't have an account?
                    <a href="/Medical Outreach Tracker/register.php" class="auth-switch-link">Sign up</a>
                </p>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>

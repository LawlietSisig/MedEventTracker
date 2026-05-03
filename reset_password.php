<?php
/**
 * Reset Password Page
 * Medical Outreach Tracker
 * Accessed via token link sent by email
 */

require_once __DIR__ . '/includes/session.php';
redirectIfLoggedIn();

require_once __DIR__ . '/config/database.php';

$flash = getFlash();
$token = trim($_GET['token'] ?? '');

// Validate token immediately
$tokenValid = false;
$tokenEmail = '';

if ($token) {
    $conn = getConnection();
    $stmt = $conn->prepare(
        "SELECT email, expires_at FROM password_resets WHERE token = ? LIMIT 1"
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($row && strtotime($row['expires_at']) > time()) {
        $tokenValid = true;
        $tokenEmail = $row['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reset Password — Medical Outreach Tracker">
    <title>Reset Password — Medical Outreach Tracker</title>
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
                <p>Choose a strong password to keep your account safe.</p>
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
                    <h2>Set new password</h2>
                    <p>Enter and confirm your new password below</p>
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

                <?php if (!$tokenValid): ?>
                <!-- Invalid / Expired Token -->
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>This reset link is invalid or has expired. Please request a new one.</span>
                </div>
                <p class="auth-switch" style="margin-top: var(--space-6);">
                    <a href="/Medical Outreach Tracker/forgot_password.php" class="auth-switch-link">Request a new reset link</a>
                </p>
                <?php else: ?>
                <!-- Valid Token — show form -->
                <form action="handlers/reset_password_handler.php" method="POST" id="reset-form">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </span>
                            <input type="password" class="form-input" id="password" name="password"
                                   placeholder="Minimum 8 characters" required minlength="8"
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" id="pw-toggle-1" aria-label="Toggle visibility">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </span>
                            <input type="password" class="form-input" id="confirm_password" name="confirm_password"
                                   placeholder="Re-enter your new password" required minlength="8"
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" id="pw-toggle-2" aria-label="Toggle visibility">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                        <span class="field-error" id="err-confirm" style="font-size:0.8rem;color:var(--danger-600);margin-top:4px;display:block;"></span>
                    </div>

                    <button type="submit" class="btn-primary" id="submit-btn">
                        <span>
                            <span class="btn-text">Reset Password</span>
                        </span>
                    </button>
                </form>

                <script>
                // Password toggle buttons
                [['pw-toggle-1','password'],['pw-toggle-2','confirm_password']].forEach(([btnId, inputId]) => {
                    document.getElementById(btnId)?.addEventListener('click', () => {
                        const inp = document.getElementById(inputId);
                        inp.type = inp.type === 'password' ? 'text' : 'password';
                    });
                });
                // Confirm match check
                document.getElementById('reset-form').addEventListener('submit', function(e) {
                    const pw  = document.getElementById('password').value;
                    const cpw = document.getElementById('confirm_password').value;
                    const err = document.getElementById('err-confirm');
                    if (pw !== cpw) {
                        e.preventDefault();
                        err.textContent = 'Passwords do not match.';
                    }
                });
                </script>
                <?php endif; ?>

                <p class="auth-switch" style="margin-top: var(--space-4);">
                    <a href="/Medical Outreach Tracker/index.php" class="auth-switch-link">Back to Sign in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

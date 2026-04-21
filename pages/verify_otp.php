<?php
/**
 * Medical Outreach Tracker — OTP Verification Page
 */

require_once __DIR__ . '/../includes/session.php';

// Must have a pending registration in session
if (empty($_SESSION['pending_email'])) {
    header('Location: /Medical Outreach Tracker/register.php');
    exit();
}

$pendingEmail = $_SESSION['pending_email'];
$pendingName  = $_SESSION['pending_name'] ?? '';

// Mask the email for display: j***@gmail.com
$parts    = explode('@', $pendingEmail);
$masked   = substr($parts[0], 0, 1) . str_repeat('*', max(1, strlen($parts[0]) - 1)) . '@' . $parts[1];

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Verify your email address to complete registration on Medical Outreach Tracker.">
    <title>Verify Email — Medical Outreach Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="42" height="42">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </div>
                <h1>Almost <span>there!</span></h1>
                <p>We sent a 6-digit verification code to your email. Check your inbox — it expires in 10 minutes.</p>

                <div class="brand-stats">
                    <div class="stat-item">
                        <span class="stat-number">🔒</span>
                        <span class="stat-label">Secure</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">6</span>
                        <span class="stat-label">Digit Code</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">10m</span>
                        <span class="stat-label">Expires in</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right OTP Form Panel -->
        <div class="login-form-panel">
            <div class="login-form-container">
                <div class="login-form-header">
                    <div class="mobile-logo" style="display:flex;">
                        <span class="mobile-logo-text">Medical Outreach Tracker</span>
                    </div>

                    <h2 style="display: flex; align-items: center; gap: 0.5rem;">
                        <div class="otp-icon" style="margin-bottom: 0; width: 36px; height: 36px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        Check your email
                    </h2>
                    <p>
                        We sent a 6-digit code to<br>
                        <strong class="otp-email"><?php echo htmlspecialchars($masked); ?></strong>
                    </p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" id="flash-alert">
                        <?php if ($flash['type'] === 'error'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <form action="../handlers/verify_otp_handler.php" method="POST" id="otp-form">
                    <div class="otp-boxes-label">Enter verification code</div>

                    <div class="otp-boxes" id="otp-boxes" style="margin-bottom: var(--space-4);">
                        <input
                            type="text"
                            class="otp-single-box"
                            id="otp_code"
                            name="otp_code"
                            maxlength="6"
                            placeholder="• • • • • •"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            autocomplete="one-time-code"
                            required
                        >
                    </div>

                    <!-- Countdown timer -->
                    <div class="otp-timer" id="otp-timer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Code expires in <span id="countdown">10:00</span>
                    </div>

                    <button type="submit" class="btn-primary" id="verify-btn">
                        <span>
                            <span class="btn-text">Verify Email</span>
                        </span>
                    </button>
                </form>

                <!-- Resend form -->
                <div class="otp-resend">
                    <span class="otp-resend-text">Didn't receive it?</span>
                    <form action="../handlers/resend_otp_handler.php" method="POST" style="display:inline;">
                        <button type="submit" class="btn-resend" id="resend-btn" disabled>
                            Resend code <span id="resend-countdown">(60s)</span>
                        </button>
                    </form>
                </div>

                <p class="auth-switch">
                    <a href="/Medical Outreach Tracker/register.php" class="auth-switch-link">
                        ← Back to registration
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="../assets/js/verify_otp.js"></script>
</body>
</html>

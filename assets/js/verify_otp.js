/**
 * OTP Verification Page Interactions
 * Medical Outreach Tracker
 */

document.addEventListener('DOMContentLoaded', () => {
    const otpInput = document.getElementById('otp_code');
    const form       = document.getElementById('otp-form');
    const verifyBtn  = document.getElementById('verify-btn');
    const resendBtn  = document.getElementById('resend-btn');
    const resendCountdownEl = document.getElementById('resend-countdown');
    const countdownEl = document.getElementById('countdown');

    // ── OTP box behaviour ───────────────────────────────────
    if (otpInput) {
        // Only allow digits
        otpInput.addEventListener('input', (e) => {
            otpInput.value = otpInput.value.replace(/\D/g, '');
            otpInput.classList.remove('otp-box-error');
        });

        // ── Paste support ────────────────────────────────────────
        otpInput.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);
            
            otpInput.value = pasted;
        });

        // ── Form submit ──────────────────────────────────────────
        if (form) {
            form.addEventListener('submit', (e) => {
                if (otpInput.value.length < 6) {
                    e.preventDefault();
                    otpInput.classList.add('otp-box-error');
                    return;
                }
                verifyBtn.disabled = true;
            });
        }
    }

    // ── 10-minute expiry countdown ───────────────────────────
    let expirySeconds = 600;

    function updateCountdown() {
        const m = Math.floor(expirySeconds / 60);
        const s = expirySeconds % 60;
        countdownEl.textContent = `${m}:${String(s).padStart(2, '0')}`;

        if (expirySeconds <= 60) {
            countdownEl.classList.add('countdown-urgent');
        }

        if (expirySeconds <= 0) {
            countdownEl.textContent = 'Expired';
            countdownEl.classList.add('countdown-expired');
            verifyBtn.disabled = true;
            verifyBtn.title = 'Code has expired — please resend or go back to register.';
            clearInterval(expiryInterval);
        } else {
            expirySeconds--;
        }
    }

    updateCountdown();
    const expiryInterval = setInterval(updateCountdown, 1000);

    // ── 60-second resend cooldown ────────────────────────────
    let resendSeconds = 60;
    resendBtn.disabled = true;

    function updateResend() {
        if (resendSeconds > 0) {
            resendCountdownEl.textContent = `(${resendSeconds}s)`;
            resendSeconds--;
        } else {
            resendBtn.disabled = false;
            resendCountdownEl.textContent = '';
            clearInterval(resendInterval);
        }
    }

    updateResend();
    const resendInterval = setInterval(updateResend, 1000);

    // ── Auto-dismiss flash ────────────────────────────────────
    const flash = document.getElementById('flash-alert');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }, 5000);
    }

    // Focus input on load
    if (otpInput) {
        otpInput.focus();
    }
});

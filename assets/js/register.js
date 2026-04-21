/**
 * Register Page Interactions
 * Medical Outreach Tracker
 */

document.addEventListener('DOMContentLoaded', () => {
    const form            = document.getElementById('register-form');
    const registerBtn     = document.getElementById('register-btn');
    const passwordInput   = document.getElementById('password');
    const confirmInput    = document.getElementById('confirm_password');
    const passwordToggle  = document.getElementById('password-toggle');
    const confirmToggle   = document.getElementById('confirm-toggle');
    const eyeIcon         = document.getElementById('eye-icon');
    const eyeIconConfirm  = document.getElementById('eye-icon-confirm');
    const strengthFill    = document.getElementById('strength-fill');
    const strengthLabel   = document.getElementById('strength-label');

    // ── Password toggle (main) ──────────────────────────────
    function makeToggle(btn, input, icon) {
        if (!btn || !input) return;
        btn.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.innerHTML = show
                ? `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />`
                : `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                   <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />`;
        });
    }
    makeToggle(passwordToggle, passwordInput, eyeIcon);
    makeToggle(confirmToggle,  confirmInput,  eyeIconConfirm);

    // ── Password strength meter ─────────────────────────────
    function getStrength(pw) {
        let score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score;
    }

    const strengthClasses = ['', 'weak', 'fair', 'good', 'strong', 'very-strong'];
    const strengthLabels  = ['', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];

    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            const val   = passwordInput.value;
            const score = val.length === 0 ? 0 : Math.max(1, getStrength(val));

            strengthFill.className  = 'strength-fill ' + (strengthClasses[score] || '');
            strengthLabel.textContent = val.length === 0 ? '' : strengthLabels[score];
            strengthLabel.className = 'strength-label ' + (strengthClasses[score] || '');
        });
    }

    // ── Inline field validation helpers ────────────────────
    function showError(fieldId, message) {
        const el = document.getElementById(fieldId + '-error');
        const input = document.getElementById(fieldId);
        if (el) el.textContent = message;
        if (input) input.classList.add('input-invalid');
    }

    function clearError(fieldId) {
        const el = document.getElementById(fieldId + '-error');
        const input = document.getElementById(fieldId);
        if (el) el.textContent = '';
        if (input) input.classList.remove('input-invalid');
    }

    // Live confirm-password match check
    if (confirmInput) {
        confirmInput.addEventListener('input', () => {
            if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                showError('confirm_password', 'Passwords do not match.');
            } else {
                clearError('confirm_password');
            }
        });
    }

    // ── Form submission ─────────────────────────────────────
    if (form) {
        form.addEventListener('submit', (e) => {
            let valid = true;

            // Clear previous errors
            ['first_name', 'last_name', 'email', 'role', 'password', 'confirm_password']
                .forEach(clearError);

            const firstName = document.getElementById('first_name').value.trim();
            const lastName  = document.getElementById('last_name').value.trim();
            const email     = document.getElementById('email').value.trim();
            const role      = document.getElementById('role').value;
            const password  = passwordInput.value;
            const confirm   = confirmInput.value;

            if (!firstName) { showError('first_name', 'First name is required.'); valid = false; }
            if (!lastName)  { showError('last_name',  'Last name is required.');  valid = false; }

            if (!email) {
                showError('email', 'Email is required.');
                valid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email', 'Please enter a valid email.');
                valid = false;
            }

            if (!role) { showError('role', 'Please select a role.'); valid = false; }

            if (password.length < 8) {
                showError('password', 'Password must be at least 8 characters.');
                valid = false;
            }

            if (password !== confirm) {
                showError('confirm_password', 'Passwords do not match.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
                return;
            }

            registerBtn.disabled = true;
        });
    }

    // ── Auto-dismiss flash alerts ───────────────────────────
    const flashAlert = document.getElementById('flash-alert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            setTimeout(() => flashAlert.remove(), 500);
        }, 5000);
    }

    // ── Focus animations ────────────────────────────────────
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', () => input.closest('.form-group').classList.add('focused'));
        input.addEventListener('blur',  () => input.closest('.form-group').classList.remove('focused'));
    });
});

/**
 * Login Page Interactions
 * Medical Outreach Tracker
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    const loginBtn = document.getElementById('login-btn');
    const passwordToggle = document.getElementById('password-toggle');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    // Password visibility toggle
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            
            // Swap icon
            if (isPassword) {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                `;
            }
        });
    }

    // Form submission with loading state
    if (form) {
        form.addEventListener('submit', (e) => {
            const email = document.getElementById('email').value.trim();
            const password = passwordInput.value.trim();

            if (!email || !password) {
                e.preventDefault();
                return;
            }

            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
        });
    }

    // Auto-dismiss flash alerts
    const flashAlert = document.getElementById('flash-alert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            setTimeout(() => flashAlert.remove(), 500);
        }, 5000);
    }

    // Add subtle input focus animations
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.closest('.form-group').classList.add('focused');
        });
        input.addEventListener('blur', () => {
            input.closest('.form-group').classList.remove('focused');
        });
    });
});

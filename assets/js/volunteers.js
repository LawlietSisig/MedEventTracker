/* 
 * Volunteers JS
 * Handles modals, validation, and interactivity
 */

const volunteerModal = document.getElementById('volunteer-modal');
const deleteModal = document.getElementById('delete-modal');

function openOverlay(overlay) {
    overlay.classList.add('modal-visible');
    document.body.style.overflow = 'hidden';
}

function closeOverlay(overlay) {
    overlay.classList.remove('modal-visible');
    document.body.style.overflow = '';
}

function closeModal()       { closeOverlay(volunteerModal); }
function closeDeleteModal() { closeOverlay(deleteModal); }

/* Click outside to close */
[volunteerModal, deleteModal].forEach(overlay => {
    if(!overlay) return;
    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeOverlay(overlay);
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        if (volunteerModal && volunteerModal.classList.contains('modal-visible')) closeModal();
        if (deleteModal && deleteModal.classList.contains('modal-visible')) closeDeleteModal();
    }
});

function clearValidation() {
    document.querySelectorAll('#volunteer-form .form-input').forEach(el => {
        el.classList.remove('input-invalid');
    });
    document.querySelectorAll('#volunteer-form .field-error').forEach(el => {
        el.textContent = '';
    });
}

function openCreateModal() {
    document.getElementById('modal-title').textContent = 'New Volunteer Profile';
    document.getElementById('modal-submit-btn').querySelector('.btn-text').textContent = 'Add Volunteer';
    document.getElementById('form-action').value   = 'create';
    document.getElementById('form-volunteer-id').value = '';
    
    document.getElementById('volunteer-form').reset();
    clearValidation();
    openOverlay(volunteerModal);
}

function openEditModal(v) {
    document.getElementById('modal-title').textContent = 'Edit Volunteer Profile';
    document.getElementById('modal-submit-btn').querySelector('.btn-text').textContent = 'Save Changes';
    document.getElementById('form-action').value   = 'update';
    document.getElementById('form-volunteer-id').value = v.id;
    
    clearValidation();
    
    document.getElementById('f-fname').value       = v.first_name   ?? '';
    document.getElementById('f-lname').value       = v.last_name    ?? '';
    document.getElementById('f-email').value       = v.email        ?? '';
    document.getElementById('f-contact').value     = v.contact_number ?? '';
    document.getElementById('f-profession').value  = v.profession   ?? '';
    document.getElementById('f-status').value      = v.status       ?? 'active';
    document.getElementById('f-notes').value       = v.skills_notes ?? '';
    
    openOverlay(volunteerModal);
}

function confirmDelete(id, name) {
    document.getElementById('delete-volunteer-id').value = id;
    document.getElementById('delete-volunteer-name').textContent = name;
    openOverlay(deleteModal);
}

/* ── Form Validation ─────────────────────────────────────────────────────────── */
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('input-invalid');
    const errEl = field.closest('.form-group')?.querySelector('.field-error')
                || field.parentElement?.querySelector('.field-error');
    if (errEl) errEl.textContent = message;
}

function validateForm() {
    let valid = true;
    clearValidation();

    const fname      = document.getElementById('f-fname').value.trim();
    const lname      = document.getElementById('f-lname').value.trim();
    const email      = document.getElementById('f-email').value.trim();
    const profession = document.getElementById('f-profession').value.trim();
    
    if (!fname) {
        showFieldError('f-fname', 'First name is required.');
        valid = false;
    }
    
    if (!lname) {
        showFieldError('f-lname', 'Last name is required.');
        valid = false;
    }
    
    if (!email) {
        showFieldError('f-email', 'Email address is required.');
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showFieldError('f-email', 'Please enter a valid email address.');
        valid = false;
    }
    
    if (!profession) {
        showFieldError('f-profession', 'Profession or role is required.');
        valid = false;
    }
    
    return valid;
}

const form = document.getElementById('volunteer-form');
if (form) {
    form.addEventListener('submit', function (e) {
        if (!validateForm()) {
            e.preventDefault();
            return;
        }
        const btn = document.getElementById('modal-submit-btn');
        btn.disabled = true;
    });

    ['f-fname', 'f-lname', 'f-email', 'f-profession'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                el.classList.remove('input-invalid');
                const errEl = el.closest('.form-group')?.querySelector('.field-error');
                if (errEl) errEl.textContent = '';
            });
        }
    });
}

/* ── Live search debounce ───────────────────────────────────────────────────── */
let searchTimer;
const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            document.getElementById('search-form').submit();
        }, 500);
    });
    const len = searchInput.value.length;
    if (len) {
        searchInput.focus();
        searchInput.setSelectionRange(len, len);
    }
}

/* ── Flash Alert Dismissal ───────────────────────────────────────────────────── */
const flashAlert = document.getElementById('flash-alert');
if (flashAlert) {
    setTimeout(() => {
        flashAlert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        flashAlert.style.opacity    = '0';
        flashAlert.style.transform  = 'translateY(-8px)';
        setTimeout(() => flashAlert.remove(), 500);
    }, 5000);
}

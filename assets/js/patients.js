/* 
 * Patients JS
 * Handles modals, validation, and interactivity
 */

const patientModal = document.getElementById('patient-modal');
const deleteModal = document.getElementById('delete-modal');

function openOverlay(overlay) {
    overlay.classList.add('modal-visible');
    // Lock scroll on body
    document.body.style.overflow = 'hidden';
}

function closeOverlay(overlay) {
    overlay.classList.remove('modal-visible');
    // Restore scroll
    document.body.style.overflow = '';
}

function closeModal()       { closeOverlay(patientModal); }
function closeDeleteModal() { closeOverlay(deleteModal); }

/* Click outside to close */
[patientModal, deleteModal].forEach(overlay => {
    if(!overlay) return;
    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeOverlay(overlay);
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        if (patientModal && patientModal.classList.contains('modal-visible')) closeModal();
        if (deleteModal && deleteModal.classList.contains('modal-visible')) closeDeleteModal();
    }
});

function clearValidation() {
    document.querySelectorAll('#patient-form .form-input').forEach(el => {
        el.classList.remove('input-invalid');
    });
    document.querySelectorAll('#patient-form .field-error').forEach(el => {
        el.textContent = '';
    });
}

function openCreateModal() {
    document.getElementById('modal-title').textContent = 'New Patient Profile';
    document.getElementById('modal-submit-btn').querySelector('.btn-text').textContent = 'Register Patient';
    document.getElementById('form-action').value   = 'create';
    document.getElementById('form-patient-id').value = '';
    
    document.getElementById('patient-form').reset();
    clearValidation();
    openOverlay(patientModal);
}

function openEditModal(p) {
    document.getElementById('modal-title').textContent = 'Edit Patient Profile';
    document.getElementById('modal-submit-btn').querySelector('.btn-text').textContent = 'Save Changes';
    document.getElementById('form-action').value   = 'update';
    document.getElementById('form-patient-id').value = p.id;
    
    clearValidation();
    
    document.getElementById('f-fname').value       = p.first_name   ?? '';
    document.getElementById('f-lname').value       = p.last_name    ?? '';
    document.getElementById('f-dob').value         = p.dob          ?? '';
    document.getElementById('f-gender').value      = p.gender       ?? 'Other';
    document.getElementById('f-contact').value     = p.contact_number ?? '';
    document.getElementById('f-blood').value       = p.blood_type   ?? '';
    document.getElementById('f-address').value     = p.address      ?? '';
    document.getElementById('f-notes').value       = p.medical_notes ?? '';
    
    openOverlay(patientModal);
}

function confirmDelete(id, name) {
    document.getElementById('delete-patient-id').value = id;
    document.getElementById('delete-patient-name').textContent = name;
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

    const fname   = document.getElementById('f-fname').value.trim();
    const lname   = document.getElementById('f-lname').value.trim();
    const dob     = document.getElementById('f-dob').value;
    
    if (!fname) {
        showFieldError('f-fname', 'First name is required.');
        valid = false;
    }
    
    if (!lname) {
        showFieldError('f-lname', 'Last name is required.');
        valid = false;
    }
    
    if (!dob) {
        showFieldError('f-dob', 'Date of birth is required.');
        valid = false;
    }

    // Basic date check (cannot be in the future)
    if (dob) {
        const selectedDate = new Date(dob);
        const now = new Date();
        if (selectedDate > now) {
            showFieldError('f-dob', 'Date of birth cannot be in the future.');
            valid = false;
        }
    }
    
    return valid;
}

const form = document.getElementById('patient-form');
if (form) {
    form.addEventListener('submit', function (e) {
        if (!validateForm()) {
            e.preventDefault();
            return;
        }
        const btn = document.getElementById('modal-submit-btn');
        btn.disabled = true;
    });

    ['f-fname', 'f-lname', 'f-dob', 'f-contact'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                el.classList.remove('input-invalid');
                const errEl = el.closest('.form-group')?.querySelector('.field-error');
                if (errEl) errEl.textContent = '';
            });
            el.addEventListener('change', () => {
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
    // Move cursor to end of text on focus
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

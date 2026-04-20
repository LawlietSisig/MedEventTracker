/**
 * Outreach Events JS
 * Medical Outreach Tracker
 */

/* ── Modal helpers ──────────────────────────────────────────────────────────── */
const eventModal  = document.getElementById('event-modal');
const deleteModal = document.getElementById('delete-modal');

function openCreateModal() {
    document.getElementById('modal-title').textContent = 'New Outreach Event';
    document.getElementById('modal-submit-btn').querySelector('.btn-text').textContent = 'Create Event';
    document.getElementById('form-action').value   = 'create';
    document.getElementById('form-event-id').value = '';
    document.getElementById('event-form').reset();
    document.getElementById('f-status').value = 'upcoming';
    clearValidation();
    openModal(eventModal);
    // Focus first field for accessibility
    setTimeout(() => document.getElementById('f-title').focus(), 80);
}

function openEditModal(ev) {
    document.getElementById('modal-title').textContent = 'Edit Outreach Event';
    document.getElementById('modal-submit-btn').querySelector('.btn-text').textContent = 'Save Changes';
    document.getElementById('form-action').value   = 'update';
    document.getElementById('form-event-id').value = ev.id;

    document.getElementById('f-title').value       = ev.title        ?? '';
    document.getElementById('f-description').value = ev.description  ?? '';
    document.getElementById('f-location').value    = ev.location     ?? '';
    document.getElementById('f-date').value        = ev.event_date   ?? '';
    document.getElementById('f-start').value       = (ev.start_time  ?? '').slice(0, 5);
    document.getElementById('f-end').value         = (ev.end_time    ?? '').slice(0, 5);
    document.getElementById('f-status').value      = ev.status       ?? 'upcoming';
    document.getElementById('f-volunteers').value  = ev.max_volunteers ?? '';

    clearValidation();
    openModal(eventModal);
}

function confirmDelete(id, name) {
    document.getElementById('delete-event-id').value = id;
    document.getElementById('delete-event-name').textContent = name;
    openModal(deleteModal);
}

function closeModal()       { closeOverlay(eventModal); }
function closeDeleteModal() { closeOverlay(deleteModal); }

function openModal(overlay) {
    overlay.classList.add('modal-visible');
    document.body.style.overflow = 'hidden';
}
function closeOverlay(overlay) {
    overlay.classList.remove('modal-visible');
    document.body.style.overflow = '';
}

/* Click outside to close */
[eventModal, deleteModal].forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeOverlay(overlay);
    });
});

/* Esc key */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
    }
});

/* ── Client-side validation ─────────────────────────────────────────────────── */

function clearValidation() {
    document.querySelectorAll('#event-form .form-input').forEach(el => {
        el.classList.remove('input-invalid');
    });
    document.querySelectorAll('#event-form .field-error').forEach(el => {
        el.textContent = '';
    });
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('input-invalid');
    // Look for sibling or nearby field-error element
    const errEl = field.closest('.form-group')?.querySelector('.field-error')
                || field.parentElement?.querySelector('.field-error');
    if (errEl) errEl.textContent = message;
}

function validateForm() {
    clearValidation();
    let valid = true;

    const title     = document.getElementById('f-title').value.trim();
    const location  = document.getElementById('f-location').value.trim();
    const date      = document.getElementById('f-date').value;
    const start     = document.getElementById('f-start').value;
    const end       = document.getElementById('f-end').value;
    const volunteers = document.getElementById('f-volunteers').value;

    if (!title) {
        showFieldError('f-title', 'Event title is required.');
        valid = false;
    } else if (title.length > 255) {
        showFieldError('f-title', 'Title must be 255 characters or less.');
        valid = false;
    }

    if (!location) {
        showFieldError('f-location', 'Location is required.');
        valid = false;
    }

    if (!date) {
        showFieldError('f-date', 'Event date is required.');
        valid = false;
    }

    if (!start) {
        showFieldError('f-start', 'Start time is required.');
        valid = false;
    }

    if (!end) {
        showFieldError('f-end', 'End time is required.');
        valid = false;
    }

    if (start && end && end <= start) {
        showFieldError('f-end', 'End time must be after start time.');
        valid = false;
    }

    if (volunteers !== '' && (isNaN(parseInt(volunteers, 10)) || parseInt(volunteers, 10) < 1)) {
        showFieldError('f-volunteers', 'Enter a valid number of volunteers (minimum 1).');
        valid = false;
    }

    return valid;
}

/* ── Form submit ────────────────────────────────────────────────────────────── */
document.getElementById('event-form').addEventListener('submit', function (e) {
    if (!validateForm()) {
        e.preventDefault();
        return;
    }
    const btn = document.getElementById('modal-submit-btn');
    btn.classList.add('loading');
    btn.disabled = true;
});

/* Clear individual field errors on input */
['f-title', 'f-description', 'f-location', 'f-date', 'f-start', 'f-end', 'f-status', 'f-volunteers'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('input', () => {
            el.classList.remove('input-invalid');
            const errEl = el.closest('.form-group')?.querySelector('.field-error')
                        || el.parentElement?.querySelector('.field-error');
            if (errEl) errEl.textContent = '';
        });
        el.addEventListener('change', () => {
            el.classList.remove('input-invalid');
            const errEl = el.closest('.form-group')?.querySelector('.field-error')
                        || el.parentElement?.querySelector('.field-error');
            if (errEl) errEl.textContent = '';
        });
    }
});

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
}

/* ── Auto-dismiss flash ─────────────────────────────────────────────────────── */
const flashAlert = document.getElementById('flash-alert');
if (flashAlert) {
    setTimeout(() => {
        flashAlert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        flashAlert.style.opacity    = '0';
        flashAlert.style.transform  = 'translateY(-8px)';
        setTimeout(() => flashAlert.remove(), 500);
    }, 5000);
}

/* ── Card entrance animation (stagger) ─────────────────────────────────────── */
document.querySelectorAll('.oe-card').forEach((card, i) => {
    card.style.animationDelay = `${i * 40}ms`;
    card.style.animationFillMode = 'both';
});

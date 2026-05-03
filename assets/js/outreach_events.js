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
    document.getElementById('f-cancelled').checked = false;
    updateCancelLabel();
    clearValidation();
    openModal(eventModal);
    setTimeout(() => document.getElementById('f-title').focus(), 80);
}

function openEditModal(ev) {
    document.getElementById('modal-title').textContent = 'Edit Outreach Event';
    document.getElementById('modal-submit-btn').querySelector('.btn-text').textContent = 'Save Changes';
    document.getElementById('form-action').value   = 'update';
    document.getElementById('form-event-id').value = ev.id;

    document.getElementById('f-title').value       = ev.title        ?? '';
    document.getElementById('f-description').value = ev.description  ?? '';
    let bCity = ev.location ?? '';
    let venueStr = '';
    if (bCity.includes(' | ')) {
        const parts = bCity.split(' | ');
        bCity = parts[0];
        venueStr = parts.slice(1).join(' | ');
    }
    document.getElementById('f-barangay-city').value = bCity;
    document.getElementById('f-venue').value      = venueStr;
    document.getElementById('f-date').value       = ev.event_date      ?? '';
    document.getElementById('f-end-date').value   = ev.end_event_date  ?? '';
    document.getElementById('f-start').value      = (ev.start_time  ?? '').slice(0, 5);
    document.getElementById('f-end').value        = (ev.end_time    ?? '').slice(0, 5);
    document.getElementById('f-cancelled').checked = (ev.status === 'cancelled');
    document.getElementById('f-volunteers').value = ev.max_volunteers ?? '';

    updateCancelLabel();
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

/* ── Cancel checkbox styling ────────────────────────────────────────────────── */
function updateCancelLabel() {
    const cb    = document.getElementById('f-cancelled');
    const label = document.getElementById('cancel-label');
    if (!cb || !label) return;
    if (cb.checked) {
        label.style.background    = 'var(--rose-50, #fff1f2)';
        label.style.borderColor   = 'var(--rose-300, #fda4af)';
        label.querySelector('span').style.color = 'var(--rose-700, #be123c)';
    } else {
        label.style.background    = 'var(--slate-50)';
        label.style.borderColor   = 'var(--slate-200)';
        label.querySelector('span').style.color = 'var(--slate-600)';
    }
}
document.getElementById('f-cancelled')?.addEventListener('change', updateCancelLabel);

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
    const errEl = field.closest('.form-group')?.querySelector('.field-error')
                || field.parentElement?.querySelector('.field-error');
    if (errEl) errEl.textContent = message;
}

function validateForm() {
    clearValidation();
    let valid = true;

    const title      = document.getElementById('f-title').value.trim();
    const bCity      = document.getElementById('f-barangay-city').value.trim();
    const venueStr   = document.getElementById('f-venue').value.trim();
    const date       = document.getElementById('f-date').value;
    const endDate    = document.getElementById('f-end-date').value;
    const start      = document.getElementById('f-start').value;
    const end        = document.getElementById('f-end').value;
    const volunteers = document.getElementById('f-volunteers').value;

    if (!title) {
        showFieldError('f-title', 'Event title is required.');
        valid = false;
    } else if (title.length > 255) {
        showFieldError('f-title', 'Title must be 255 characters or less.');
        valid = false;
    }

    if (!bCity) {
        showFieldError('f-barangay-city', 'Barangay, City is required.');
        valid = false;
    }
    if (!venueStr) {
        showFieldError('f-venue', 'Venue is required.');
        valid = false;
    }

    if (!date) {
        showFieldError('f-date', 'Start date is required.');
        valid = false;
    } else {
        const action = document.getElementById('form-action').value;
        if (action === 'create') {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const [y, m, d] = date.split('-');
            const selectedDate = new Date(y, m - 1, d);
            selectedDate.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                showFieldError('f-date', 'Start date must be today or in the future.');
                valid = false;
            }
        }
    }

    // End date must be >= start date if provided
    if (endDate && date && endDate < date) {
        showFieldError('f-end-date', 'End date cannot be before the start date.');
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
    document.getElementById('modal-submit-btn').disabled = true;
});

/* Clear individual field errors on input */
['f-title', 'f-description', 'f-barangay-city', 'f-venue', 'f-date', 'f-end-date', 'f-start', 'f-end', 'f-volunteers'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        ['input', 'change'].forEach(evt => {
            el.addEventListener(evt, () => {
                el.classList.remove('input-invalid');
                const errEl = el.closest('.form-group')?.querySelector('.field-error')
                            || el.parentElement?.querySelector('.field-error');
                if (errEl) errEl.textContent = '';
            });
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

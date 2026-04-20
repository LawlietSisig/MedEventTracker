<?php
/**
 * Mail Configuration — Gmail SMTP
 * Medical Outreach Tracker
 *
 * HOW TO GET A GMAIL APP PASSWORD:
 * 1. Go to https://myaccount.google.com/security
 * 2. Make sure 2-Step Verification is ON
 * 3. Search "App passwords" in your Google Account
 * 4. Create a new app password → Select "Mail" and "Windows Computer"
 * 5. Copy the 16-character password and paste it below (no spaces)
 */

// ── Your Gmail address ──────────────────────────────────────
define('MAIL_USERNAME', 'grealle.sjeeg.d.masuki@gmail.com');

// ── The 16-character App Password from Google ───────────────
define('MAIL_PASSWORD', 'hevt kyas fgxl cavm');

// ── Display name shown in the "From" field ──────────────────
define('MAIL_FROM_NAME', 'Medical Outreach Tracker');

// ── Gmail SMTP — do not change these ────────────────────────
define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM',       MAIL_USERNAME);

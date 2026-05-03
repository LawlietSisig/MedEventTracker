<?php
/**
 * Mailer Utility
 * Wraps PHPMailer for sending OTP emails
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an OTP verification email
 *
 * @param string $toEmail  Recipient email address
 * @param string $toName   Recipient display name
 * @param string $otpCode  The plain-text 6-digit OTP
 * @return array ['success' => bool, 'message' => string]
 */
function sendOtpEmail(string $toEmail, string $toName, string $otpCode): array {
    $mail = new PHPMailer(true);

    try {
        // ── SMTP settings ──────────────────────────────────
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;

        // ── Sender & recipient ─────────────────────────────
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // ── Content ────────────────────────────────────────
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code — Medical Outreach Tracker';
        $mail->Body    = buildOtpEmailHtml($toName, $otpCode);
        $mail->AltBody = buildOtpEmailText($toName, $otpCode);

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully.'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Email could not be sent: ' . $mail->ErrorInfo];
    }
}

/**
 * Build the HTML body of the OTP email
 */
function buildOtpEmailHtml(string $name, string $otp): string {
    $digits = str_split($otp);
    $digitBoxes = '';
    foreach ($digits as $d) {
        $digitBoxes .= '<td style="padding:0 4px;"><div style="width:44px;height:52px;background:#f1f5f9;border:2px solid #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#0c8f8f;font-family:monospace;text-align:center;line-height:52px;">' . $d . '</div></td>';
    }

    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Inter',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:40px 20px;">
    <tr><td align="center">
      <table width="520" cellpadding="0" cellspacing="0" style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#0f172a 0%,#033939 50%,#097272 100%);padding:36px 40px;text-align:center;">
            <div style="display:inline-block;width:56px;height:56px;background:rgba(255,255,255,0.12);border-radius:14px;line-height:56px;font-size:28px;margin-bottom:16px;">🏥</div>
            <h1 style="margin:0;color:white;font-size:22px;font-weight:800;letter-spacing:-0.5px;">Medical Outreach Tracker</h1>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <h2 style="margin:0 0 8px;color:#0f172a;font-size:20px;font-weight:700;">Verify your email address</h2>
            <p style="margin:0 0 28px;color:#64748b;font-size:15px;line-height:1.6;">
              Hi {$name}, use the code below to verify your email and complete your account registration.
              This code expires in <strong>10 minutes</strong>.
            </p>

            <!-- OTP Boxes -->
            <table cellpadding="0" cellspacing="0" style="margin:0 auto 32px;">
              <tr>{$digitBoxes}</tr>
            </table>

            <p style="margin:0 0 24px;color:#94a3b8;font-size:13px;text-align:center;">
              If you didn't request this, you can safely ignore this email.
            </p>
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 24px;">
            <p style="margin:0;color:#cbd5e1;font-size:12px;text-align:center;">
              © Medical Outreach Tracker — Community Health Management System
            </p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

/**
 * Plain-text fallback for the OTP email
 */
function buildOtpEmailText(string $name, string $otp): string {
    return "Hi {$name},\n\nYour verification code is: {$otp}\n\nThis code expires in 10 minutes.\n\nIf you didn't request this, ignore this email.\n\n— Medical Outreach Tracker";
}

/**
 * Send a password-reset email with a secure link
 *
 * @param string $toEmail    Recipient email
 * @param string $toName     Recipient display name
 * @param string $resetLink  Full reset URL
 * @return array ['success' => bool, 'message' => string]
 */
function sendPasswordResetEmail(string $toEmail, string $toName, string $resetLink): array {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password — Medical Outreach Tracker';
        $mail->Body    = buildResetEmailHtml($toName, $resetLink);
        $mail->AltBody = buildResetEmailText($toName, $resetLink);

        $mail->send();
        return ['success' => true, 'message' => 'Reset email sent.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo];
    }
}

function buildResetEmailHtml(string $name, string $link): string {
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Inter',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:40px 20px;">
    <tr><td align="center">
      <table width="520" cellpadding="0" cellspacing="0" style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#0f172a 0%,#033939 50%,#097272 100%);padding:36px 40px;text-align:center;">
            <div style="display:inline-block;width:56px;height:56px;background:rgba(255,255,255,0.12);border-radius:14px;line-height:56px;font-size:28px;margin-bottom:16px;">🔒</div>
            <h1 style="margin:0;color:white;font-size:22px;font-weight:800;letter-spacing:-0.5px;">Medical Outreach Tracker</h1>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <h2 style="margin:0 0 8px;color:#0f172a;font-size:20px;font-weight:700;">Reset your password</h2>
            <p style="margin:0 0 28px;color:#64748b;font-size:15px;line-height:1.6;">
              Hi {$name}, we received a request to reset your password. Click the button below to set a new one.
              This link expires in <strong>1 hour</strong>.
            </p>
            <table cellpadding="0" cellspacing="0" style="margin:0 auto 32px;">
              <tr>
                <td style="background:#097272;border-radius:10px;">
                  <a href="{$link}" style="display:inline-block;padding:14px 32px;color:white;font-size:15px;font-weight:700;text-decoration:none;letter-spacing:0.3px;">Reset Password</a>
                </td>
              </tr>
            </table>
            <p style="margin:0 0 8px;color:#94a3b8;font-size:13px;text-align:center;">Or copy this link into your browser:</p>
            <p style="margin:0 0 24px;color:#64748b;font-size:12px;text-align:center;word-break:break-all;">{$link}</p>
            <p style="margin:0 0 24px;color:#94a3b8;font-size:13px;text-align:center;">If you didn't request this, you can safely ignore this email.</p>
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 24px;">
            <p style="margin:0;color:#cbd5e1;font-size:12px;text-align:center;">&copy; Medical Outreach Tracker — Community Health Management System</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

function buildResetEmailText(string $name, string $link): string {
    return "Hi {$name},\n\nWe received a request to reset your password.\n\nClick the link below (or paste it into your browser) to set a new password:\n{$link}\n\nThis link expires in 1 hour.\n\nIf you didn't request this, ignore this email.\n\n— Medical Outreach Tracker";
}

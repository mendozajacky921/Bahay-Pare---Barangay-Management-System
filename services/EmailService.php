<?php

declare(strict_types=1);

namespace Services;

use PHPMailer\PHPMailer\PHPMailer;
// HIGH-01 / LOW-02: Removed unused 'use PHPMailer\PHPMailer\SMTP' import
use PHPMailer\PHPMailer\Exception as MailException;

class EmailService
{
    private function mailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION === 'tls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        return $mail;
    }

    /**
     * Send a transactional email using an HTML template.
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        try {
            $mail = $this->mailer();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);
            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log(
                sprintf("[%s] EmailService error to %s: %s\n", date('Y-m-d H:i:s'), $toEmail, $e->getMessage()),
                3,
                ROOT_PATH . '/storage/logs/app.log'
            );
            return false;
        }
    }

    // ── Email Templates ───────────────────────────────────

    /**
     * HIGH-01 fix: the original body used the literal string '{barangayName}'
     * as a placeholder inside a double-quoted string — it was never interpolated
     * because PHP does not expand {key} as a variable unless it's {$var}.
     * Fixed by referencing the BARANGAY_NAME constant directly.
     */
    public function sendWelcome(string $email, string $name): bool
    {
        $barangayName = BARANGAY_NAME;
        $subject = "Welcome to {$barangayName} — Verify Your Email";
        $html    = $this->template('Welcome', $name, "
            <p>Thank you for registering with the <strong>{$barangayName}</strong> Management System.</p>
            <p>Please verify your email address by clicking the link sent to your inbox from our system.</p>
            <p>Once verified, our staff will review your registration. You will receive another email once your account is approved.</p>
        ");
        return $this->send($email, $name, $subject, $html);
    }

    public function sendRequestReceived(string $email, string $name, string $requestType, string $trackingId): bool
    {
        $typeLabel = $this->formatRequestType($requestType);
        $subject   = "Request Received — {$typeLabel}";
        $html      = $this->template('Request Received', $name, "
            <p>We have received your request for a <strong>{$typeLabel}</strong>.</p>
            <p><strong>Tracking ID:</strong> " . htmlspecialchars($trackingId, ENT_QUOTES, 'UTF-8') . "</p>
            <p>You can track the status of your request by logging in to your account.</p>
            <p>Processing time depends on staff availability. You will be notified of any status changes via email.</p>
        ");
        return $this->send($email, $name, $subject, $html);
    }

    public function sendStatusUpdate(
        string $email,
        string $name,
        string $requestType,
        string $newStatus,
        ?string $reason = null
    ): bool {
        $typeLabel   = $this->formatRequestType($requestType);
        $statusLabel = match ($newStatus) {
            'under_review' => 'Under Review',
            'approved'     => 'Approved',
            'rejected'     => 'Rejected',
            'released'     => 'Released — Ready for Pickup',
            default        => ucfirst($newStatus),
        };

        $subject = "Request Update: {$statusLabel} — {$typeLabel}";
        $body    = "<p>Your request for a <strong>{$typeLabel}</strong> has been updated.</p>
                    <p><strong>New Status:</strong> {$statusLabel}</p>";

        if ($reason) {
            $body .= '<p><strong>Note:</strong> ' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        if ($newStatus === 'released') {
            $body .= '<p>Please visit the barangay hall during office hours to claim your document. Bring a valid ID.</p>';
        }

        if ($newStatus === 'rejected') {
            $body .= '<p>If you have questions, please visit the barangay hall or contact us.</p>';
        }

        $html = $this->template('Request Status Update', $name, $body);
        return $this->send($email, $name, $subject, $html);
    }

    public function sendAccountApproved(string $email, string $name): bool
    {
        $subject = 'Account Approved — ' . BARANGAY_NAME;
        $loginUrl = htmlspecialchars(APP_URL . '/login', ENT_QUOTES, 'UTF-8');
        $html    = $this->template('Account Approved', $name, "
            <p>Your account has been verified and approved by our staff.</p>
            <p>You can now log in and request barangay documents online.</p>
            <p>
              <a href=\"{$loginUrl}\"
                 style=\"background:#1d4ed8;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;\">
                Log In Now
              </a>
            </p>
        ");
        return $this->send($email, $name, $subject, $html);
    }

    // ── Base Template ─────────────────────────────────────

    private function template(string $heading, string $recipientName, string $body): string
    {
        $barangayName  = htmlspecialchars(BARANGAY_NAME, ENT_QUOTES, 'UTF-8');
        $year          = date('Y');
        $appUrl        = htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8');
        $safeHeading   = htmlspecialchars($heading,       ENT_QUOTES, 'UTF-8');
        $safeName      = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$safeHeading}</title>
        </head>
        <body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 20px;">
            <tr><td align="center">
              <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1);">
                <tr>
                  <td style="background:#1d4ed8;padding:32px 40px;text-align:center;">
                    <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;">{$barangayName}</h1>
                    <p style="margin:4px 0 0;color:#bfdbfe;font-size:13px;">Barangay Management System</p>
                  </td>
                </tr>
                <tr>
                  <td style="padding:40px;">
                    <h2 style="margin:0 0 8px;color:#1e293b;font-size:20px;">{$safeHeading}</h2>
                    <p style="margin:0 0 24px;color:#64748b;font-size:14px;">Hello, {$safeName}</p>
                    <div style="color:#334155;font-size:15px;line-height:1.7;">
                      {$body}
                    </div>
                  </td>
                </tr>
                <tr>
                  <td style="background:#f8fafc;padding:24px 40px;border-top:1px solid #e2e8f0;text-align:center;">
                    <p style="margin:0;color:#94a3b8;font-size:12px;">
                      &copy; {$year} {$barangayName} &bull;
                      <a href="{$appUrl}/privacy-policy" style="color:#64748b;">Privacy Policy</a>
                    </p>
                    <p style="margin:4px 0 0;color:#cbd5e1;font-size:11px;">This is an automated message. Please do not reply.</p>
                  </td>
                </tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }

    private function formatRequestType(string $type): string
    {
        return match ($type) {
            'barangay_clearance'       => 'Barangay Clearance',
            'certificate_of_residency' => 'Certificate of Residency',
            'certificate_of_indigency' => 'Certificate of Indigency',
            'cedula'                   => 'Community Tax Certificate (Cedula)',
            'barangay_id'              => 'Barangay ID',
            default                    => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}

<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Herald
{
    /**
     * Send forth a message of good report unto the appointed recipient.
     *
     * @param string $unto The email address of the recipient.
     * @param string $title The subject of the epistle.
     * @param string $scripture The body of the message.
     * @param array $seal Optional headers or additional mail options (unused here).
     * @return bool True if the message was dispatched successfully.
     */
    public function proclaim(string $unto, string $title, string $scripture, array $seal = []): bool
    {
        $mail = new PHPMailer(true);

        try {

            // Heavenly SMTP settings — fetched from environment
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'] ?: 'localhost';
            $mail->Port = $_ENV['MAIL_PORT'] ?: 25;
            $mail->SMTPAuth = $_ENV['MAIL_SMTP_AUTH'] === 'true' ? true : false;
            $mail->Username = $_ENV['MAIL_USERNAME'] ?: '';
            $mail->Password = $_ENV['MAIL_PASSWORD'] ?: '';
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?: ''; // e.g. 'tls' or 'ssl'
            if (empty($mail->SMTPSecure)) {
                $mail->SMTPAutoTLS = false; // disable if no encryption
            }

            // Sender of the good news
            $fromAddress = getenv('MAIL_FROM_ADDRESS') ?: 'messenger@praywall.org';
            $fromName = getenv('MAIL_FROM_NAME') ?: 'Messenger';
            $mail->setFrom($fromAddress, $fromName);

            // Recipient to receive the message
            $mail->addAddress($unto);

            // The message itself
            $mail->Subject = $title;
            $mail->Body = $scripture;

            // Send the message to the faithful
            $mail->send();

            return true;
        } catch (Exception $e) {
            error_log("Failed to send mail to {$unto}: " . $mail->ErrorInfo);
            return false;
        }
    }
}

<?php
namespace App\Services;

class Herald
{
    /**
     * Send forth a message of good report unto the appointed recipient.
     *
     * @param string $unto The email address of the recipient.
     * @param string $title The subject of the epistle.
     * @param string $scripture The body of the message.
     * @param string $seal Optional headers for the message.
     * @return bool True if the message was dispatched successfully.
     */
    public function proclaim(string $unto, string $title, string $scripture, string $seal = ''): bool
    {
        $fromAddress = getenv('MAIL_FROM_ADDRESS');
        $fromName = getenv('MAIL_FROM_NAME') ?: 'Messenger';
        $heavenlySeal = "From: {$fromName} <{$fromAddress}>\r\n";

        $success = mail($unto, $title, $scripture, $seal ?: $heavenlySeal);

        if (!$success) {
            error_log("Failed to send mail to $unto");
        }

        return $success;
    }
}

namespace App\Services;

class Mailer
{
    public function send($to, $subject, $message, $headers = '')
    {
        $defaultHeaders = "From: no-reply@example.com\r\n";
        return mail($to, $subject, $message, $headers ?: $defaultHeaders);
    }
}

<?php
require_once __DIR__ . '/config.php';

echo "DOTENV Loaded check:\n";
print_r($_ENV);
echo "\nSERVER vars check for SMTP:\n";
print_r($_SERVER);

echo "Checking SMTP Configuration...\n";
echo "SMTP_HOST (const): " . (defined('SMTP_HOST') ? SMTP_HOST : 'NOT DEFINED') . "\n";
echo "FROM_EMAIL (const): " . (defined('FROM_EMAIL') ? FROM_EMAIL : 'NOT DEFINED') . "\n";

use App\Services\MailService;

$service = new MailService();
$to = 'test_recipient@example.com'; 
$result = $service->send($to, 'Test Email from CLI', 'This is a test email sent synchronously from CLI.');


if ($result) {
    echo "Send returned TRUE. Check inbox of $to (or server logs).\n";
} else {
    echo "Send returned FALSE.\n";
}

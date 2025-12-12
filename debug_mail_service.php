<?php
require_once __DIR__ . '/config.php';
use App\Services\MailService;

echo "DB Host (config): " . DB_HOST . "\n";
echo "DB Name (config): " . DB_NAME . "\n";
echo "DB User (config): " . DB_USER . "\n";
// Don't echo password

echo "Testing MailService connection...\n";

$service = new MailService();
// We can't easily check private property $pdo, but we can try to queue a message.
// If queue returns false, it failed.

$result = $service->queue('test@example.com', 'Test Subject', 'Test Body');

if ($result) {
    echo "Queue successful!\n";
} else {
    echo "Queue FAILED.\n";
    // Check error log?
}

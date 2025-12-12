<?php
require_once __DIR__ . '/config.php';

$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM mail_queue ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll();

echo "Latest 5 Mail Queue Entries:\n";
foreach ($rows as $row) {
    echo "ID: {$row['id']}, Status: {$row['status']}, To: {$row['to_email']}, Created: {$row['created_at']}, Sent: {$row['sent_at']}\n";
    if ($row['status'] === 'failed') {
        echo "Error: {$row['error_message']}\n";
    }
}

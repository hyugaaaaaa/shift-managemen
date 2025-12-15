<?php
require_once __DIR__ . '/config.php';

// Debug log (using helper function)
write_log("Process triggered", 'mail_process.log');

// MailServiceのインスタンス化と実行
use App\Services\MailService;

echo "[" . date('Y-m-d H:i:s') . "] Starting mail queue processing...\n";

$service = new MailService();
$count = $service->processQueue();
write_log("Processed $count emails.", 'mail_process.log');

echo "[" . date('Y-m-d H:i:s') . "] Processed $count emails.\n";
